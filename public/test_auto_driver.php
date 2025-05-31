<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;

echo "<h1>Intervention Image Auto-Driver Test</h1>";

try {
    // Try to create an image manager without specifying a driver
    echo "<p>Attempting to create an ImageManager without specifying a driver...</p>";
    
    // For Intervention Image v3, we need to use a driver
    // Let's try a different approach - check which drivers are available
    $drivers = [];
    
    if (extension_loaded('gd')) {
        $drivers[] = 'GD';
    }
    
    if (extension_loaded('imagick')) {
        $drivers[] = 'Imagick';
    }
    
    echo "<p>Available drivers: " . implode(', ', $drivers) . "</p>";
    
    if (empty($drivers)) {
        echo "<p style='color:red'>No image processing drivers available! Please install GD or Imagick.</p>";
        exit;
    }
    
    // Use GD if available, otherwise try Imagick
    if (in_array('GD', $drivers)) {
        echo "<p>Using GD driver...</p>";
        $driver = new \Intervention\Image\Drivers\Gd\Driver();
    } else {
        echo "<p>Using Imagick driver...</p>";
        $driver = new \Intervention\Image\Drivers\Imagick\Driver();
    }
    
    $manager = new ImageManager($driver);
    
    // Create a simple image
    $image = $manager->create(300, 300, '#ff0000');
    
    // Save it to disk
    $path = __DIR__ . '/auto-driver-test.jpg';
    $image->toJpeg()->save($path);
    
    echo "<p style='color:green'>Success! Image created at: {$path}</p>";
    echo "<p><img src='/auto-driver-test.jpg' width='300' height='300' alt='Test Image'></p>";
} catch (\Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 