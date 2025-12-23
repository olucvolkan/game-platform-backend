<?php

namespace App\Console\Commands;

use App\Services\IgdbService;
use Illuminate\Console\Command;
use Exception;

/**
 * Test command to verify IGDB API connection and query format.
 *
 * Usage:
 *   php artisan igdb:test
 */
class TestIgdbConnection extends Command
{
    protected $signature = 'igdb:test';

    protected $description = 'Test IGDB API connection and query format';

    public function handle(IgdbService $igdbService): int
    {
        $this->info('IGDB API Connection Test');
        $this->info('========================');
        $this->newLine();

        // Step 1: Check credentials
        $this->info('Step 1: Checking credentials...');
        $clientId = config('igdb.client_id');
        $clientSecret = config('igdb.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('IGDB credentials not configured!');
            $this->warn('Please set IGDB_CLIENT_ID and IGDB_CLIENT_SECRET in your .env file');
            $this->newLine();
            $this->info('To get credentials:');
            $this->line('1. Go to https://dev.twitch.tv/console/apps');
            $this->line('2. Register a new application');
            $this->line('3. Copy the Client ID');
            $this->line('4. Generate a Client Secret');
            return self::FAILURE;
        }

        $this->info("  Client ID: " . substr($clientId, 0, 10) . '...');
        $this->info("  Client Secret: " . substr($clientSecret, 0, 5) . '***');
        $this->newLine();

        // Step 2: Test OAuth token
        $this->info('Step 2: Obtaining OAuth token...');
        try {
            $token = $igdbService->getAccessToken();
            $this->info("  Token obtained: " . substr($token, 0, 20) . '...');
            $this->newLine();
        } catch (Exception $e) {
            $this->error('Failed to obtain OAuth token!');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        // Step 3: Test simple query
        $this->info('Step 3: Testing simple query (fields name;limit 5;)...');
        try {
            $results = $igdbService->testConnection();
            $this->info("  Results received: " . count($results) . " games");

            if (count($results) > 0) {
                $this->newLine();
                $this->info('  Sample games:');
                foreach ($results as $game) {
                    $this->line("    - " . ($game['name'] ?? 'Unknown'));
                }
            }
            $this->newLine();
        } catch (Exception $e) {
            $this->error('Simple query failed!');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        // Step 4: Test full query
        $this->info('Step 4: Testing full query with covers...');
        try {
            $results = $igdbService->fetchGames(5, 0, 0);
            $this->info("  Results received: " . count($results) . " games");

            if (count($results) > 0) {
                $this->newLine();
                $this->info('  Games with details:');
                foreach ($results as $game) {
                    $name = $game['name'] ?? 'Unknown';
                    $rating = isset($game['total_rating']) ? round($game['total_rating'], 1) : 'N/A';
                    $hasCover = !empty($game['cover']['image_id']) ? 'Yes' : 'No';
                    $this->line("    - {$name} (Rating: {$rating}, Cover: {$hasCover})");
                }
            } else {
                $this->warn('  No games returned! This might indicate a query issue.');
            }
            $this->newLine();
        } catch (Exception $e) {
            $this->error('Full query failed!');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        // Step 5: Test search
        $this->info('Step 5: Testing search (query: "zelda")...');
        try {
            $results = $igdbService->searchGames('zelda', 3);
            $this->info("  Search results: " . count($results) . " games");

            if (count($results) > 0) {
                foreach ($results as $game) {
                    $this->line("    - " . ($game['name'] ?? 'Unknown'));
                }
            }
            $this->newLine();
        } catch (Exception $e) {
            $this->warn('Search query failed: ' . $e->getMessage());
        }

        $this->info('All tests completed successfully!');
        $this->newLine();
        $this->info('You can now run: php artisan igdb:import');

        return self::SUCCESS;
    }
}
