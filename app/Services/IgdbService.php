<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Exception;

/**
 * Service for interacting with the IGDB (Internet Game Database) API.
 *
 * IGDB uses Twitch OAuth for authentication and a custom query language
 * called Apicalypse for requests.
 *
 * @see https://api-docs.igdb.com/
 */
class IgdbService
{
    /**
     * Cached access token for the current request.
     */
    protected ?string $accessToken = null;

    /**
     * Timestamp of the last API request (for rate limiting).
     */
    protected float $lastRequestTime = 0;

    /**
     * Get a valid OAuth access token from Twitch.
     *
     * Tokens are cached for efficiency since they last ~60 days.
     *
     * @return string
     * @throws Exception
     */
    public function getAccessToken(): string
    {
        // Return cached instance token if available
        if ($this->accessToken) {
            return $this->accessToken;
        }

        // Try to get from cache
        $cacheKey = config('igdb.token_cache_key');
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken) {
            $this->accessToken = $cachedToken;
            return $this->accessToken;
        }

        // Request new token from Twitch
        $this->accessToken = $this->requestNewToken();

        // Cache the token
        Cache::put(
            $cacheKey,
            $this->accessToken,
            config('igdb.token_cache_ttl', 86400 * 30)
        );

        return $this->accessToken;
    }

    /**
     * Request a new OAuth token from Twitch.
     *
     * @return string
     * @throws Exception
     */
    protected function requestNewToken(): string
    {
        $clientId = config('igdb.client_id');
        $clientSecret = config('igdb.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception(
                'IGDB credentials not configured. Set IGDB_CLIENT_ID and IGDB_CLIENT_SECRET in .env'
            );
        }

        $response = Http::timeout(config('igdb.timeout', 30))
            ->asForm()
            ->post(config('igdb.token_url'), [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('IGDB token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('Failed to obtain IGDB access token: ' . $response->body());
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw new Exception('IGDB token response missing access_token');
        }

        Log::info('IGDB access token obtained successfully', [
            'expires_in' => $data['expires_in'] ?? 'unknown',
        ]);

        return $data['access_token'];
    }

    /**
     * Make a query to the IGDB API.
     *
     * IMPORTANT: Apicalypse query format requirements:
     * - Each clause ends with semicolon
     * - No newlines between clauses (single line preferred)
     * - Fields separated by commas without spaces after comma
     * - Body sent as raw text
     *
     * @param string $endpoint The API endpoint (e.g., 'games', 'covers', 'genres')
     * @param string $body The Apicalypse query body
     * @return array
     * @throws Exception
     */
    public function query(string $endpoint, string $body): array
    {
        $this->enforceRateLimit();

        $token = $this->getAccessToken();
        $url = rtrim(config('igdb.base_url'), '/') . '/' . ltrim($endpoint, '/');

        // Normalize query: remove extra whitespace and newlines
        // Apicalypse works best with compact single-line queries
        $normalizedBody = $this->normalizeQuery($body);

        Log::debug('IGDB API request', [
            'endpoint' => $endpoint,
            'url' => $url,
            'body' => $normalizedBody,
        ]);

        try {
            $response = Http::timeout(config('igdb.timeout', 30))
                ->withHeaders([
                    'Client-ID' => config('igdb.client_id'),
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])
                ->withBody($normalizedBody, 'text/plain')
                ->post($url);

            $this->lastRequestTime = microtime(true);

            Log::debug('IGDB API response', [
                'status' => $response->status(),
                'body_length' => strlen($response->body()),
            ]);

            if (!$response->successful()) {
                // Check if token expired
                if ($response->status() === 401) {
                    Log::warning('IGDB token expired, refreshing...');
                    $this->clearCachedToken();
                    return $this->query($endpoint, $body);
                }

                Log::error('IGDB API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'query' => $normalizedBody,
                ]);

                throw new Exception('IGDB API request failed: ' . $response->body());
            }

            $result = $response->json() ?? [];

            Log::debug('IGDB API result', [
                'count' => count($result),
            ]);

            return $result;
        } catch (RequestException $e) {
            Log::error('IGDB API request exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'query' => $normalizedBody,
            ]);
            throw new Exception('IGDB API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize Apicalypse query to single-line format.
     *
     * Converts multi-line queries to compact single-line format
     * that IGDB API expects.
     *
     * @param string $query
     * @return string
     */
    protected function normalizeQuery(string $query): string
    {
        // Replace newlines with spaces
        $query = preg_replace('/\s*\n\s*/', ' ', $query);

        // Replace multiple spaces with single space
        $query = preg_replace('/\s+/', ' ', $query);

        // Trim whitespace
        $query = trim($query);

        return $query;
    }

    /**
     * Fetch games from IGDB with related data.
     *
     * Query format follows IGDB Apicalypse syntax:
     * - Fields with dot notation for nested objects
     * - Where clause with & for AND, | for OR
     * - Parentheses for IN operator: category = (0,1,2)
     * - Comparison operators: >, <, =, !=, >=, <=
     * - null check: field != null
     *
     * @param int $limit Number of games to fetch (max 500)
     * @param int $offset Offset for pagination
     * @param int $minRating Minimum total_rating filter (default 0 for more results)
     * @return array
     * @throws Exception
     */
    public function fetchGames(int $limit = 50, int $offset = 0, int $minRating = 0): array
    {
        // Ensure limit doesn't exceed IGDB maximum
        $limit = min($limit, 500);

        // Build query with proper Apicalypse syntax
        // Note: Using simpler query first to ensure we get results
        // category: 0 = main game, 1 = DLC, 2 = expansion
        $body = "fields name,slug,summary,cover.image_id,screenshots.image_id,genres.name,first_release_date,total_rating,category,involved_companies.company.name,involved_companies.developer,involved_companies.publisher;";

        // Add where clause - start simple to ensure results
        if ($minRating > 0) {
            $body .= "where cover != null & total_rating >= {$minRating};";
        } else {
            $body .= "where cover != null;";
        }

        $body .= "sort total_rating desc;";
        $body .= "limit {$limit};";

        if ($offset > 0) {
            $body .= "offset {$offset};";
        }

        return $this->query('games', $body);
    }

    /**
     * Fetch popular games from IGDB.
     *
     * @param int $limit Number of games to fetch
     * @param int $offset Offset for pagination
     * @return array
     * @throws Exception
     */
    public function fetchPopularGames(int $limit = 50, int $offset = 0): array
    {
        $body = "fields name,slug,summary,cover.image_id,screenshots.image_id,genres.name,first_release_date,total_rating,category,involved_companies.company.name,involved_companies.developer,involved_companies.publisher;";
        $body .= "where cover != null & total_rating != null;";
        $body .= "sort total_rating desc;";
        $body .= "limit {$limit};";

        if ($offset > 0) {
            $body .= "offset {$offset};";
        }

        return $this->query('games', $body);
    }

    /**
     * Test the IGDB API connection with a simple query.
     *
     * This is useful for debugging connection issues.
     *
     * @return array
     * @throws Exception
     */
    public function testConnection(): array
    {
        // Simplest possible query - just get 5 game names
        $body = "fields name;limit 5;";

        return $this->query('games', $body);
    }

    /**
     * Fetch a single game by its IGDB ID.
     *
     * @param int $igdbId
     * @return array|null
     * @throws Exception
     */
    public function fetchGameById(int $igdbId): ?array
    {
        $body = "fields name,slug,summary,cover.image_id,screenshots.image_id,genres.name,first_release_date,total_rating,category,involved_companies.company.name,involved_companies.developer,involved_companies.publisher;";
        $body .= "where id = {$igdbId};";

        $results = $this->query('games', $body);

        return $results[0] ?? null;
    }

    /**
     * Search games by name.
     *
     * Note: IGDB search does not work with sorting.
     *
     * @param string $searchTerm
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function searchGames(string $searchTerm, int $limit = 20): array
    {
        // Escape quotes in search term
        $searchTerm = str_replace('"', '\\"', $searchTerm);

        // Note: search cannot be combined with sort in IGDB
        $body = "search \"{$searchTerm}\";";
        $body .= "fields name,slug,summary,cover.image_id,screenshots.image_id,genres.name,first_release_date,total_rating,category,involved_companies.company.name,involved_companies.developer,involved_companies.publisher;";
        $body .= "where cover != null;";
        $body .= "limit {$limit};";

        return $this->query('games', $body);
    }

    /**
     * Build a full image URL from an IGDB image ID.
     *
     * @param string $imageId The image ID from IGDB (e.g., 'co1wyy')
     * @param string $size Image size (e.g., 'cover_big', 'screenshot_big')
     * @return string
     */
    public function buildImageUrl(string $imageId, string $size = 'cover_big'): string
    {
        $baseUrl = rtrim(config('igdb.image_base_url'), '/');

        return "{$baseUrl}/t_{$size}/{$imageId}.jpg";
    }

    /**
     * Build a cover image URL.
     *
     * @param string $imageId
     * @return string
     */
    public function buildCoverUrl(string $imageId): string
    {
        return $this->buildImageUrl($imageId, config('igdb.default_cover_size', 'cover_big'));
    }

    /**
     * Build a screenshot image URL.
     *
     * @param string $imageId
     * @return string
     */
    public function buildScreenshotUrl(string $imageId): string
    {
        return $this->buildImageUrl($imageId, config('igdb.default_screenshot_size', 'screenshot_big'));
    }

    /**
     * Enforce rate limiting to stay within IGDB's 4 requests/second limit.
     */
    protected function enforceRateLimit(): void
    {
        $rateLimit = config('igdb.rate_limit', 4);
        $minInterval = 1.0 / $rateLimit; // Minimum seconds between requests

        $now = microtime(true);
        $elapsed = $now - $this->lastRequestTime;

        if ($elapsed < $minInterval) {
            $sleepTime = (int) (($minInterval - $elapsed) * 1000000);
            usleep($sleepTime);
        }
    }

    /**
     * Clear the cached access token.
     */
    public function clearCachedToken(): void
    {
        $this->accessToken = null;
        Cache::forget(config('igdb.token_cache_key'));
    }

    /**
     * Extract developer name from involved_companies.
     *
     * @param array $involvedCompanies
     * @return string|null
     */
    public function extractDeveloper(array $involvedCompanies): ?string
    {
        foreach ($involvedCompanies as $company) {
            if (!empty($company['developer']) && !empty($company['company']['name'])) {
                return $company['company']['name'];
            }
        }

        return null;
    }

    /**
     * Extract publisher name from involved_companies.
     *
     * @param array $involvedCompanies
     * @return string|null
     */
    public function extractPublisher(array $involvedCompanies): ?string
    {
        foreach ($involvedCompanies as $company) {
            if (!empty($company['publisher']) && !empty($company['company']['name'])) {
                return $company['company']['name'];
            }
        }

        return null;
    }

    /**
     * Extract genre names from genres array.
     *
     * @param array $genres
     * @return array
     */
    public function extractGenreNames(array $genres): array
    {
        return array_filter(array_map(function ($genre) {
            return $genre['name'] ?? null;
        }, $genres));
    }

    /**
     * Convert IGDB category to product type.
     *
     * IGDB Categories:
     * 0 = Main game
     * 1 = DLC/Addon
     * 2 = Expansion
     * 3 = Bundle
     * 4 = Standalone expansion
     *
     * @param int|null $category
     * @return string
     */
    public function categoryToProductType(?int $category): string
    {
        return match ($category) {
            1, 2, 4 => 'DLC',
            3 => 'Bundle',
            default => 'Game',
        };
    }
}
