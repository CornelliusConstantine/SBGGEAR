<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

echo "<h1>GD Extension Diagnostic Tool</h1>";

// Check if GD is loaded
if (extension_loaded('gd')) {
    echo "<p style='color:green'>✓ GD extension is loaded in PHP</p>";
    echo "<p>GD Version: " . gd_info()['GD Version'] . "</p>";
    
    // Check if php.ini has GD enabled
    $php_ini_path = php_ini_loaded_file();
    echo "<p>PHP INI Path: " . $php_ini_path . "</p>";
    
    $ini_content = file_get_contents($php_ini_path);
    if (strpos($ini_content, 'extension=gd') !== false) {
        $gd_line = preg_grep('/extension=gd/', explode("\n", $ini_content));
        $gd_line = reset($gd_line);
        if (strpos($gd_line, ';') === 0) {
            echo "<p style='color:red'>✗ GD extension is commented out in php.ini: " . htmlspecialchars($gd_line) . "</p>";
        } else {
            echo "<p style='color:green'>✓ GD extension is properly enabled in php.ini: " . htmlspecialchars($gd_line) . "</p>";
        }
    } else {
        echo "<p style='color:red'>✗ GD extension not found in php.ini</p>";
    }
    
    // Check for Intervention Image
    echo "<h2>Intervention Image Check</h2>";
    
    if (class_exists('Intervention\Image\ImageManager')) {
        echo "<p style='color:green'>✓ Intervention\Image\ImageManager class exists</p>";
    } else {
        echo "<p style='color:red'>✗ Intervention\Image\ImageManager class not found</p>";
    }
    
    if (class_exists('Intervention\Image\Drivers\Gd\Driver')) {
        echo "<p style='color:green'>✓ Intervention\Image\Drivers\Gd\Driver class exists</p>";
    } else {
        echo "<p style='color:red'>✗ Intervention\Image\Drivers\Gd\Driver class not found</p>";
    }
    
    // Try creating an image
    try {
        $manager = new ImageManager(new Driver());
        $image = $manager->create(100, 100, '#0000ff');
        $path = __DIR__ . '/fix-test.jpg';
        $image->toJpeg()->save($path);
        
        echo "<p style='color:green'>✓ Successfully created test image: " . $path . "</p>";
        echo "<p><img src='/fix-test.jpg' width='100' height='100' alt='Test Image'></p>";
    } catch (\Exception $e) {
        echo "<p style='color:red'>✗ Error creating image: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<p style='color:red'>✗ GD extension is NOT loaded in PHP</p>";
    
    // Show loaded extensions
    echo "<h2>Loaded Extensions</h2>";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo "<pre>" . implode(", ", $extensions) . "</pre>";
    
    // Check php.ini
    $php_ini_path = php_ini_loaded_file();
    echo "<p>PHP INI Path: " . $php_ini_path . "</p>";
    
    // Try to fix the issue
    echo "<h2>Fixing Attempt</h2>";
    echo "<p>To fix this issue:</p>";
    echo "<ol>";
    echo "<li>Edit your php.ini file at: " . $php_ini_path . "</li>";
    echo "<li>Find the line with <code>;extension=gd</code> and remove the semicolon</li>";
    echo "<li>Save the file and restart your web server</li>";
    echo "</ol>";
} 