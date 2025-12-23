<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter([
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        env('FRONTEND_URL'),
    ]),

    'allowed_origins_patterns' => [
        // Allow all Laravel Cloud subdomains
        '#^https://.*\.laravel\.cloud$#',
        // Allow Vercel preview deployments
        '#^https://.*\.vercel\.app$#',
        // Allow Netlify preview deployments
        '#^https://.*\.netlify\.app$#',
    ],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,

];
