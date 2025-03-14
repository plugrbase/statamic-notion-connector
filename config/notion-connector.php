<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notion API Configuration
    |--------------------------------------------------------------------------
    */
    'notion' => [
        'auth_token' => env('NOTION_AUTH_TOKEN'),
        'version' => '2022-06-28', // Fixed version that we know works
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    */
    'sync' => [
        'schedule' => env('NOTION_SYNC_SCHEDULE', '0 * * * *'), // Default: hourly
        'chunk_size' => env('NOTION_SYNC_CHUNK_SIZE', 100),
        'rate_limit' => [
            'requests_per_second' => 3,
            'concurrent_requests' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => false, // Disabled by default for debugging
        'ttl' => 3600, // 1 hour
    ],
]; 