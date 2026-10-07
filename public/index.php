<?php

// Check PHP version
if (!version_compare(phpversion(), '8.3.0', '>=')) {
    echo 'PHP 8.3+ is required to run FreeScout. Your PHP version: '.phpversion();
    exit();
}

if (preg_match("#^/public\/(.*)#", $_SERVER['REQUEST_URI'], $m) && !empty($m[1])) {
    header("Location: /".$m[1]);
    exit();
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(App\Http\Request::capture());
