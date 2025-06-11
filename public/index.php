<?php

// Check if this is an API request
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    // Handle API requests with Laravel
    define('LARAVEL_START', microtime(true));

    /*
    |--------------------------------------------------------------------------
    | Check If The Application Is Under Maintenance
    |--------------------------------------------------------------------------
    |
    | If the application is in maintenance / demo mode via the "down" command
    | we will load this file so that any pre-rendered content can be shown
    | instead of starting the framework, which could cause an exception.
    |
    */

    if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
        require $maintenance;
    }

    /*
    |--------------------------------------------------------------------------
    | Register The Auto Loader
    |--------------------------------------------------------------------------
    |
    | Composer provides a convenient, automatically generated class loader for
    | this application. We just need to utilize it! We'll simply require it
    | into the script here so we don't need to manually load our classes.
    |
    */

    require __DIR__.'/../vendor/autoload.php';

    /*
    |--------------------------------------------------------------------------
    | Run The Application
    |--------------------------------------------------------------------------
    |
    | Once we have the application, we can handle the incoming request using
    | the application's HTTP kernel. Then, we will send the response back
    | to this client's browser, allowing them to enjoy our application.
    |
    */

    $app = require_once __DIR__.'/../bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    )->send();
    $kernel->terminate($request, $response);
    exit;
}

// Check if the requested file exists
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    // If it's a PHP file, execute it
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
        require_once __DIR__ . $uri;
        exit;
    }
    
    // If it's a static file, let the server handle it
    return false;
}

// For all other requests, serve the AngularJS app
include_once __DIR__ . '/index.html';
exit;