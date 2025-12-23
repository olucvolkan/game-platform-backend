<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Genre;
use App\Models\GameScreenshot;
use App\Services\IgdbService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Artisan command to import games from IGDB.
 *
 * Usage:
 *   php artisan igdb:import              # Import 100 games
 *   php artisan igdb:import --count=200  # Import 200 games
 *   php artisan igdb:import --skip=50    # Skip first 50 games (offset)
 *   php artisan igdb:import --min-rating=70  # Only games with rating > 70
 *
 * Features:
 * - Fetches games in batches (50 at a time) to respect rate limits
 * - Generates missing data (prices, cashback, regions)
 * - Creates genres if they don't exist
 * - Imports screenshots
 * - Skips duplicates by IGDB ID
 * - Progress bar for CLI feedback
 */
class ImportGamesFromIgdb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igdb:import
                            {--count=100 : Number of games to import}
                            {--skip=0 : Number of games to skip (offset)}
                            {--min-rating=60 : Minimum rating threshold}
                            {--batch-size=50 : Games per API request (max 500)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import games from IGDB (Internet Game Database)';

    /**
     * Available platforms for random assignment.
     */
    protected array $platforms = ['Steam', 'Xbox', 'PlayStation', 'Nintendo', 'Epic', 'GOG'];

    /**
     * Available regions for random assignment.
     */
    protected array $regions = ['GLOBAL', 'EU', 'US', 'TR'];

    /**
     * Available discount percentages.
     */
    protected array $discounts = [0, 0, 0, 10, 15, 20, 25, 30, 33, 40, 50];

    /**
     * Statistics counters.
     */
    protected int $imported = 0;
    protected int $skipped = 0;
    protected int $failed = 0;

    /**
     * Execute the console command.
     */
    public function handle(IgdbService $igdbService): int
    {
        $count = (int) $this->option('count');
        $skip = (int) $this->option('skip');
        $minRating = (int) $this->option('min-rating');
        $batchSize = min((int) $this->option('batch-size'), 500);

        $this->info("IGDB Game Import");
        $this->info("================");
        $this->info("Importing up to {$count} games with rating > {$minRating}");
        $this->info("Starting offset: {$skip}, Batch size: {$batchSize}");
        $this->newLine();

        // Verify credentials
        try {
            $this->info('Authenticating with IGDB...');
            $igdbService->getAccessToken();
            $this->info('Authentication successful!');
            $this->newLine();
        } catch (Exception $e) {
            $this->error('Authentication failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Calculate batches
        $totalBatches = (int) ceil($count / $batchSize);
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $progressBar->setMessage('Starting...');
        $progressBar->start();

        $offset = $skip;
        $gamesProcessed = 0;

        for ($batch = 0; $batch < $totalBatches && $gamesProcessed < $count; $batch++) {
            $remaining = $count - $gamesProcessed;
            $limit = min($batchSize, $remaining);

            $progressBar->setMessage("Fetching batch " . ($batch + 1) . "/{$totalBatches}...");

            try {
                $games = $igdbService->fetchGames($limit, $offset, $minRating);

                if (empty($games)) {
                    $progressBar->setMessage('No more games available from IGDB');
                    break;
                }

                foreach ($games as $gameData) {
                    try {
                        $this->importGame($gameData, $igdbService);
                        $gamesProcessed++;
                        $progressBar->advance();

                        if ($gamesProcessed >= $count) {
                            break;
                        }
                    } catch (Exception $e) {
                        $this->failed++;
                        Log::error('Failed to import game', [
                            'igdb_id' => $gameData['id'] ?? 'unknown',
                            'name' => $gameData['name'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $offset += count($games);

            } catch (Exception $e) {
                $this->error("\nBatch fetch failed: " . $e->getMessage());
                Log::error('IGDB batch fetch failed', [
                    'batch' => $batch + 1,
                    'offset' => $offset,
                    'error' => $e->getMessage(),
                ]);
                // Continue to next batch on failure
                $offset += $batchSize;
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Import Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Games Imported', $this->imported],
                ['Games Skipped (duplicate)', $this->skipped],
                ['Games Failed', $this->failed],
                ['Total Processed', $gamesProcessed],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Import a single game from IGDB data.
     *
     * @param array $gameData
     * @param IgdbService $igdbService
     */
    protected function importGame(array $gameData, IgdbService $igdbService): void
    {
        $igdbId = $gameData['id'];

        // Check for duplicate
        if (Game::where('igdb_id', $igdbId)->exists()) {
            $this->skipped++;
            return;
        }

        DB::transaction(function () use ($gameData, $igdbService, $igdbId) {
            // Generate pricing
            $discount = $this->discounts[array_rand($this->discounts)];
            $price = $this->generatePrice();
            $originalPrice = $discount > 0 ? round($price / (1 - $discount / 100), 2) : $price;

            // Build cover image URL
            $coverUrl = '';
            if (!empty($gameData['cover']['image_id'])) {
                $coverUrl = $igdbService->buildCoverUrl($gameData['cover']['image_id']);
            }

            // Extract companies
            $involvedCompanies = $gameData['involved_companies'] ?? [];
            $developer = $igdbService->extractDeveloper($involvedCompanies);
            $publisher = $igdbService->extractPublisher($involvedCompanies);

            // Parse release date
            $releaseDate = null;
            if (!empty($gameData['first_release_date'])) {
                $releaseDate = date('Y-m-d', $gameData['first_release_date']);
            }

            // Generate slug
            $slug = $gameData['slug'] ?? Str::slug($gameData['name']);
            $slug = $this->ensureUniqueSlug($slug);

            // Determine product type
            $productType = $igdbService->categoryToProductType($gameData['category'] ?? 0);

            // Random cashback
            $hasCashback = rand(1, 100) <= 40; // 40% chance
            $cashbackPercent = $hasCashback ? rand(5, 25) : 0;

            // Create game
            $game = Game::create([
                'igdb_id' => $igdbId,
                'slug' => $slug,
                'title' => $gameData['name'],
                'image' => $coverUrl,
                'price' => $price,
                'original_price' => $originalPrice,
                'discount' => $discount,
                'platform' => $this->platforms[array_rand($this->platforms)],
                'region' => $this->regions[array_rand($this->regions)],
                'product_type' => $productType,
                'has_cashback' => $hasCashback,
                'cashback_percent' => $cashbackPercent,
                'release_date' => $releaseDate,
                'developer' => $developer,
                'publisher' => $publisher,
                'description' => $gameData['summary'] ?? null,
                'popularity_score' => (int) ($gameData['total_rating'] ?? 0),
            ]);

            // Attach genres
            if (!empty($gameData['genres'])) {
                $genreNames = $igdbService->extractGenreNames($gameData['genres']);
                $genreIds = $this->getOrCreateGenres($genreNames);
                $game->genres()->attach($genreIds);
            }

            // Import screenshots
            if (!empty($gameData['screenshots'])) {
                $this->importScreenshots($game, $gameData['screenshots'], $igdbService);
            }

            $this->imported++;
        });
    }

    /**
     * Generate a random price in a realistic range.
     *
     * @return float
     */
    protected function generatePrice(): float
    {
        $pricePoints = [
            9.99, 14.99, 19.99, 24.99, 29.99,
            34.99, 39.99, 44.99, 49.99, 59.99, 69.99
        ];

        // Weight towards lower prices
        $weights = [20, 15, 15, 12, 10, 8, 7, 5, 4, 3, 1];
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $cumulative = 0;
        foreach ($pricePoints as $index => $price) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $price;
            }
        }

        return 29.99; // Default fallback
    }

    /**
     * Ensure the slug is unique by appending a suffix if needed.
     *
     * @param string $slug
     * @return string
     */
    protected function ensureUniqueSlug(string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (Game::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get or create genres by name.
     *
     * @param array $genreNames
     * @return array Genre IDs
     */
    protected function getOrCreateGenres(array $genreNames): array
    {
        $genreIds = [];

        foreach ($genreNames as $name) {
            $slug = Str::slug($name);

            $genre = Genre::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );

            $genreIds[] = $genre->id;
        }

        return $genreIds;
    }

    /**
     * Import screenshots for a game.
     *
     * @param Game $game
     * @param array $screenshots
     * @param IgdbService $igdbService
     */
    protected function importScreenshots(Game $game, array $screenshots, IgdbService $igdbService): void
    {
        $order = 0;

        foreach ($screenshots as $screenshot) {
            if (empty($screenshot['image_id'])) {
                continue;
            }

            $url = $igdbService->buildScreenshotUrl($screenshot['image_id']);

            GameScreenshot::create([
                'game_id' => $game->id,
                'url' => $url,
                'order' => $order++,
            ]);

            // Limit screenshots to avoid too much data
            if ($order >= 10) {
                break;
            }
        }
    }
}
