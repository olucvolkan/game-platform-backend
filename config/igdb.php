<?php

/**
 * IGDB API Configuration
 *
 * IGDB (Internet Game Database) uses Twitch OAuth for authentication.
 * Register at https://dev.twitch.tv/console/apps to get credentials.
 *
 * Rate Limits:
 * - 4 requests per second maximum
 * - Access tokens expire in ~60 days
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Twitch Client ID
    |--------------------------------------------------------------------------
    |
    | Your Twitch application's Client ID. Register at:
    | https://dev.twitch.tv/console/apps
    |
    */
    'client_id' => env('IGDB_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Twitch Client Secret
    |--------------------------------------------------------------------------
    |
    | Your Twitch application's Client Secret. Keep this secure!
    |
    */
    'client_secret' => env('IGDB_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | IGDB API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for IGDB API v4 endpoints.
    |
    */
    'base_url' => env('IGDB_BASE_URL', 'https://api.igdb.com/v4'),

    /*
    |--------------------------------------------------------------------------
    | Twitch OAuth Token URL
    |--------------------------------------------------------------------------
    |
    | The URL for obtaining OAuth access tokens from Twitch.
    |
    */
    'token_url' => env('IGDB_TOKEN_URL', 'https://id.twitch.tv/oauth2/token'),

    /*
    |--------------------------------------------------------------------------
    | Image CDN Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for IGDB image CDN. Images are constructed as:
    | {image_base_url}/t_{size}/{image_id}.jpg
    |
    | Available sizes:
    | - cover_small: 90x128
    | - cover_big: 264x374
    | - screenshot_med: 569x320
    | - screenshot_big: 889x500
    | - screenshot_huge: 1280x720
    |
    */
    'image_base_url' => env('IGDB_IMAGE_BASE_URL', 'https://images.igdb.com/igdb/image/upload'),

    /*
    |--------------------------------------------------------------------------
    | Default Image Sizes
    |--------------------------------------------------------------------------
    |
    | Default image sizes to use when building URLs.
    |
    */
    'default_cover_size' => env('IGDB_DEFAULT_COVER_SIZE', 'cover_big'),
    'default_screenshot_size' => env('IGDB_DEFAULT_SCREENSHOT_SIZE', 'screenshot_big'),

    /*
    |--------------------------------------------------------------------------
    | Access Token Cache Key
    |--------------------------------------------------------------------------
    |
    | Cache key for storing the OAuth access token.
    |
    */
    'token_cache_key' => 'igdb_access_token',

    /*
    |--------------------------------------------------------------------------
    | Token Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long to cache the access token. Tokens typically last ~60 days,
    | but we refresh a bit earlier to be safe.
    |
    */
    'token_cache_ttl' => env('IGDB_TOKEN_CACHE_TTL', 86400 * 30), // 30 days

    /*
    |--------------------------------------------------------------------------
    | Request Rate Limit
    |--------------------------------------------------------------------------
    |
    | Maximum requests per second to IGDB API.
    | IGDB allows 4 requests/second.
    |
    */
    'rate_limit' => env('IGDB_RATE_LIMIT', 4),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout in seconds.
    |
    */
    'timeout' => env('IGDB_TIMEOUT', 30),
];
