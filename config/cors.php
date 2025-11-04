<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". Adjust for development and production accordingly.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Sob HTTP method allow

    'allowed_origins' => env('APP_ENV') === 'production'
        ? ['https://yourfrontend.com'] // Production: specific frontend domain
        : ['*'], // Development: sob origin allow

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Sob header allow

    'exposed_headers' => [],

    'max_age' => 3600, // Preflight request 1 hour cache

    'supports_credentials' => env('APP_ENV') === 'production'
        ? true  // Production: cookies/session allow
        : false, // Development: false

];
