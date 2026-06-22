<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // In production set FRONTEND_URL to your deployed frontend origin.
    // Locally '*' is used as fallback so dev works without extra config.
    'allowed_origins' => array_filter([
        env('FRONTEND_URL'),
        env('APP_ENV') !== 'production' ? '*' : null,
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,
];
