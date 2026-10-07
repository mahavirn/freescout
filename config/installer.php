<?php

/*
|--------------------------------------------------------------------------
| Web installer
|--------------------------------------------------------------------------
*/
return [

    'core' => [
        'minPhpVersion' => '8.3.0',
        'maxPhpVersion' => '8.99.99',
    ],

    // PHP extensions required by the app.
    'requirements' => [
        'php' => [
            'OpenSSL',
            'PDO',
            'Mbstring',
            'Tokenizer',
            'JSON',
            'XML',
            'GD',
            'fileinfo',
            'ZIP',
            'iconv',
            'cURL',
            'DOM',
            'libxml',
            // 'intl' is optional, as it's only used to translate dates (see Helper::checkRequiredExtensions()).
        ],
    ],

    'permissions' => [
        'storage/app/'                      => '775',
        'storage/framework/'                => '775',
        'storage/framework/cache/data/'     => '775',
        'storage/logs/'                     => '775',
        'bootstrap/cache/'                  => '775',
        'public/css/builds/'                => '775',
        'public/js/builds/'                 => '775',
        'public/modules/'                   => '775',
        'Modules/'                          => '775',
    ],

    'environment' => [
        'form' => [
            'rules' => [
                'app_url'               => 'required|url',
                'database_connection'   => 'required|string|max:1000',
                'database_hostname'     => 'required|string|max:1000',
                'database_port'         => 'required|numeric',
                'database_name'         => 'required|string|max:1000',
                'database_username'     => 'required|string|max:1000',
                'database_password'     => 'required|string|max:1000',
                'admin_email'           => 'required|email',
                'admin_first_name'      => 'required|string|max:20',
                'admin_last_name'       => 'required|string|max:30',
                'admin_password'        => 'required|string',
            ],
        ],
    ],

];
