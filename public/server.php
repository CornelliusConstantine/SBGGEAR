<?php
/**
 * Server script for AngularJS HTML5 mode
 * 
 * This script handles routing for the AngularJS application
 * when using HTML5 mode (clean URLs without hashbang).
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Handle API requests
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/laravel.php';
    exit;
}

// Check if the requested file exists (for static assets)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    // If it's a PHP file, execute it
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
        require_once __DIR__ . $uri;
        exit;
    }
    
    // For other files, let the server handle it
    return false;
}

// For all other requests, serve the AngularJS app
include_once __DIR__ . '/index.html';
exit; 