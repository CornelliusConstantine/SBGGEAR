<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

try {
    echo "Testing Intervention Image with direct GD driver...\n";
    
    // Check if GD is installed
    if (!extension_loaded('gd')) {
        echo "GD extension is NOT loaded!\n";
        echo "Loaded extensions: " . implode(", ", get_loaded_extensions()) . "\n";
        exit(1);
    }
    
    echo "GD extension is loaded. Version: " . gd_info()['GD Version'] . "\n";
    
    // Create image manager with GD driver
    $manager = new ImageManager(new Driver());
    
    // Create a simple image
    $image = $manager->create(300, 300, '#ff0000');
    
    // Save it to disk
    $path = __DIR__ . '/public/test-direct.jpg';
    $image->toJpeg()->save($path);
    
    echo "Success! Image created at: {$path}\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 