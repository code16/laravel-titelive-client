<?php

return [
    'book_directory' => [
        'mock' => env('TITELIVE_CLIENT_MOCK_BOOK_DIRECTORY', false),
        'use_cache' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_USE_CACHE', false),
        'cache_duration' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_CACHE_DURATION_IN_MINUTES', 60 * 24),
        'api' => [
            'retry' => [
                'times' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_API_RETRY_TIMES', 5),
                'sleep_milliseconds' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_API_RETRY_SLEEP_MILLISECONDS', 2000),
            ],
        ],
    ],
];
