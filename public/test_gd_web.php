<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

// Check if GD is installed
echo "<h2>PHP GD Test</h2>";

if (extension_loaded('gd')) {
    echo "<p style='color:green'>✓ GD extension is loaded!</p>";
    echo "<p>GD Version: " . gd_info()['GD Version'] . "</p>";
    
    echo "<h3>Loaded PHP Extensions:</h3>";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo "<pre>" . implode(", ", $extensions) . "</pre>";
    
    // Try to use Intervention Image
    echo "<h3>Testing Intervention Image:</h3>";
    try {
        // Create image manager with GD driver
        $manager = new ImageManager(new Driver());
        
        // Create a simple image
        $image = $manager->create(300, 300, '#ff0000');
        
        // Save it to disk
        $path = __DIR__ . '/test-web.jpg';
        $image->toJpeg()->save($path);
        
        echo "<p style='color:green'>✓ Image created successfully at: " . $path . "</p>";
        echo "<p><img src='/test-web.jpg' width='300' height='300' alt='Test Image'></p>";
    } catch (\Exception $e) {
        echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<p style='color:red'>✗ GD extension is NOT loaded!</p>";
    
    echo "<h3>Loaded PHP Extensions:</h3>";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo "<pre>" . implode(", ", $extensions) . "</pre>";
} 