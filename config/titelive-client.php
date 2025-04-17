<?php

return [
    'book_directory' => [
        'mock' => env('TITELIVE_CLIENT_MOCK_BOOK_DIRECTORY', false),
        'use_cache' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_USE_CACHE', false),
        'cache_duration' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_CACHE_DURATION_IN_MINUTES', 60 * 24),
        'api' => [
            'login' => env('TITELIVE_LOGIN'),
            'password' => env('TITELIVE_PWD'),
            'endpoint' => env('TITELIVE_ENDPOINT', 'https://catsearch.epagine.fr/v1/'),
            'login_endpoint' => env('TITELIVE_LOGIN_ENDPOINT', 'https://login.epagine.fr/v1/'),
            'retry' => [
                'times' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_API_RETRY_TIMES', 5),
                'sleep_milliseconds' => env('TITELIVE_CLIENT_BOOK_DIRECTORY_API_RETRY_SLEEP_MILLISECONDS', 2000),
            ],
            'params' => [
                'code_support' => env('TITELIVE_CLIENT_API_PARAM_CODE_SUPPORT'),
            ],
        ],
    ],
    'book_model_class' => \Code16\LaravelTiteliveClient\Models\Book::class,
];
