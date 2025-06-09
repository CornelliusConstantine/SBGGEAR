<?php

// This file serves as a router for the PHP built-in server
// It routes API requests to Laravel and everything else to the AngularJS app

// Debug information
$debugMode = false;  // Set to true to see debug info

if ($debugMode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<pre>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "</pre>";
}

// Get the URI path
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route API requests to Laravel
if (strpos($uri, '/api/') === 0) {
    if ($debugMode) echo "<pre>Routing to API: $uri</pre>";
    require_once __DIR__ . '/laravel.php';
    return true;
}

// If the requested file exists and is not a directory, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    // Check file extension
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    
    // If it's a PHP file, execute it
    if ($ext === 'php') {
        if ($debugMode) echo "<pre>Executing PHP file: $uri</pre>";
        require_once __DIR__ . $uri;
        return true;
    }
    
    // For other files, let the built-in server handle it
    if ($debugMode) echo "<pre>Serving static file: $uri</pre>";
    return false;
}

// For all other requests, serve the AngularJS app (HTML5 mode)
if ($debugMode) echo "<pre>Serving AngularJS app for: $uri</pre>";
include_once __DIR__ . '/index.html'; 
exit; 