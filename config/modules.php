<?php

use Nwidart\Modules\Providers\ConsoleServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | FreeScout module structure used by "php artisan module:make".
    |
    */
    'stubs' => [
        'enabled' => true,
        'path'    => base_path('stubs/modules'),
        'files'   => [
            'start'           => 'start.php',
            'routes'          => 'Http/routes.php',
            'views/index'     => 'Resources/views/index.blade.php',
            'views/master'    => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'composer'        => 'composer.json',
        ],
        'replacements' => [
            'start'           => ['LOWER_NAME'],
            'routes'          => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer'        => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
            ],
        ],
        'gitkeep' => true,
    ],

    'paths' => [
        'modules'    => base_path('Modules'),
        'assets'     => public_path('modules'),
        'migration'  => base_path('database/migrations'),
        // FreeScout modules do not have "app" folder.
        'app_folder' => '',
        'generator'  => [
            'config'         => ['path' => 'Config', 'generate' => true],
            'command'        => ['path' => 'Console', 'generate' => true],
            'migration'      => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder'         => ['path' => 'Database/Seeders', 'generate' => true],
            'factory'        => ['path' => 'Database/factories', 'generate' => true],
            'model'          => ['path' => 'Entities', 'generate' => true],
            'controller'     => ['path' => 'Http/Controllers', 'generate' => true],
            'filter'         => ['path' => 'Http/Middleware', 'generate' => true],
            'request'        => ['path' => 'Http/Requests', 'generate' => true],
            'provider'       => ['path' => 'Providers', 'generate' => true],
            'route-provider' => ['path' => 'Providers', 'generate' => false],
            'event-provider' => ['path' => 'Providers', 'generate' => false],
            'assets'         => ['path' => 'Resources/assets', 'generate' => true],
            'lang'           => ['path' => 'Resources/lang', 'generate' => true],
            'views'          => ['path' => 'Resources/views', 'generate' => true],
            'test-unit'      => ['path' => 'Tests', 'generate' => true],
            'test-feature'   => ['path' => 'Tests', 'generate' => false],
            'repository'     => ['path' => 'Repositories', 'generate' => false],
            'event'          => ['path' => 'Events', 'generate' => false],
            'listener'       => ['path' => 'Listeners', 'generate' => false],
            'policies'       => ['path' => 'Policies', 'generate' => false],
            'rules'          => ['path' => 'Rules', 'generate' => false],
            'jobs'           => ['path' => 'Jobs', 'generate' => false],
            'emails'         => ['path' => 'Emails', 'generate' => false],
            'notifications'  => ['path' => 'Notifications', 'generate' => false],
            'resource'       => ['path' => 'Transformers', 'generate' => false],
            'public'         => ['path' => 'Public', 'generate' => true],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Discover
    |--------------------------------------------------------------------------
    |
    | Modules register their migrations and translations in their service providers.
    |
    */
    'auto-discover' => [
        'migrations'   => false,
        'translations' => false,
    ],

    'commands' => ConsoleServiceProvider::defaultCommands()->toArray(),

    'scan' => [
        'enabled' => false,
        'paths'   => [
            base_path('vendor/*/*'),
        ],
    ],

    'composer' => [
        'vendor' => 'freescout',
        'author' => [
            'name'  => 'FreeScout',
            'email' => 'support@freescout.net',
        ],
        'composer-output' => false,
    ],

    'register' => [
        'translations' => true,
        'files'        => 'register',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | Modules statuses are stored in the "modules" DB table.
    |
    */
    'activators' => [
        'database' => [
            'class' => App\Misc\Modules\DbActivator::class,
        ],
    ],

    'activator' => 'database',
];
