<?php

return [
    'book_directory' => [
        'mock' => env('TITELIVE_CLIENT_MOCK_BOOK_DIRECTORY', false),
        'use_cache' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_USE_CACHE', false),
        'cache_duration' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_CACHE_DURATION_IN_MINUTES', 60*24),
    ],
];
