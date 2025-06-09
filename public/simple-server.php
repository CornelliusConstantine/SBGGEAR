<?php
/**
 * Simple server script for AngularJS HTML5 mode
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Debug mode - set to true to see debug information
$debug = false;

if ($debug) {
    header('Content-Type: text/plain');
    echo "Debug Information:\n";
    echo "URI: $uri\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
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

// For all other requests, serve the simple app
include_once __DIR__ . '/simple-app.html';
exit; 