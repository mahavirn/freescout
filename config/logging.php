<?php

/*
|--------------------------------------------------------------------------
| Logging Configuration
|--------------------------------------------------------------------------
|
| Uses the same .env options as before Laravel 5.6:
| APP_LOG ("single", "daily", "syslog", "errorlog") and APP_LOG_LEVEL.
| By default logs for 5 days are kept.
|
*/

return [

    'default' => env('LOG_CHANNEL', env('APP_LOG', 'daily')),

    'deprecations' => [
        'channel' => 'null',
        'trace'   => false,
    ],

    'channels' => [
        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('APP_LOG_LEVEL', 'error'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('APP_LOG_LEVEL', 'error'),
            'days'   => env('APP_LOG_MAX_FILES', 5),
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('APP_LOG_LEVEL', 'error'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('APP_LOG_LEVEL', 'error'),
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
