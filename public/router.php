<?php

// This file serves as a router for the PHP built-in server
// It routes API requests to Laravel and everything else to the AngularJS app

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route API requests to Laravel
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/laravel.php';
    return true;
}

// If the file exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Otherwise, serve the AngularJS app
include_once __DIR__ . '/index.html'; 