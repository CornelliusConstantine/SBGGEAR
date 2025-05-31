<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP GD Extension Diagnostic</h1>";

// Get PHP ini path
$php_ini_path = php_ini_loaded_file();
echo "<p><strong>PHP INI Path:</strong> {$php_ini_path}</p>";

// Check if GD is loaded
if (extension_loaded('gd')) {
    echo "<p style='color:green'><strong>✓ GD is loaded!</strong> Version: " . gd_info()['GD Version'] . "</p>";
} else {
    echo "<p style='color:red'><strong>✗ GD is NOT loaded!</strong></p>";
}

echo "<h2>Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<pre>" . implode(", ", $extensions) . "</pre>";

// Check if we can find the php.ini file for the web server
echo "<h2>PHP INI Files Check</h2>";
$possible_paths = [
    'C:\xampp\php\php.ini',
    'C:\php\php.ini',
    'C:\Windows\php.ini',
    'C:\Program Files\PHP\php.ini',
    'C:\Program Files (x86)\PHP\php.ini',
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        echo "<p><strong>Found PHP INI at:</strong> {$path}</p>";
        
        // Check if GD is enabled in this file
        $content = file_get_contents($path);
        if (strpos($content, 'extension=gd') !== false) {
            $gd_line = preg_grep('/extension=gd/', explode("\n", $content));
            $gd_line = reset($gd_line);
            echo "<p><strong>GD line:</strong> " . htmlspecialchars(trim($gd_line)) . "</p>";
            
            if (strpos($gd_line, ';') === 0) {
                echo "<p style='color:red'><strong>✗ GD is commented out in this file.</strong></p>";
            } else {
                echo "<p style='color:green'><strong>✓ GD is enabled in this file.</strong></p>";
            }
        } else {
            echo "<p style='color:red'><strong>✗ GD extension not found in this file.</strong></p>";
        }
    }
}

// Try to find the PHP extension directory
$extension_dir = ini_get('extension_dir');
echo "<h2>PHP Extension Directory</h2>";
echo "<p><strong>Extension directory:</strong> {$extension_dir}</p>";

// Check if the GD DLL exists
$gd_dll = $extension_dir . '/php_gd.dll';
if (file_exists($gd_dll)) {
    echo "<p style='color:green'><strong>✓ GD DLL found at:</strong> {$gd_dll}</p>";
} else {
    echo "<p style='color:red'><strong>✗ GD DLL not found at:</strong> {$gd_dll}</p>";
}

// Check PHP Server API
echo "<h2>PHP Server API</h2>";
echo "<p><strong>PHP SAPI:</strong> " . php_sapi_name() . "</p>";

// Check if we can create a GD image
echo "<h2>GD Image Creation Test</h2>";
if (extension_loaded('gd')) {
    try {
        $im = imagecreatetruecolor(120, 120);
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, 5, 5, 'GD Test Image', $text_color);
        
        // Start output buffering
        ob_start();
        imagejpeg($im);
        $image_data = ob_get_clean();
        
        // Free memory
        imagedestroy($im);
        
        // Convert to base64 for display
        $base64 = base64_encode($image_data);
        echo "<p style='color:green'><strong>✓ Successfully created GD image:</strong></p>";
        echo "<p><img src='data:image/jpeg;base64,{$base64}' alt='GD Test Image'></p>";
    } catch (Exception $e) {
        echo "<p style='color:red'><strong>✗ Failed to create GD image:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'><strong>✗ Cannot test image creation - GD not loaded</strong></p>";
}

// Server information
echo "<h2>Server Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Operating System:</strong> " . PHP_OS . "</p>";

// Check for other image libraries
echo "<h2>Other Image Libraries</h2>";
if (extension_loaded('imagick')) {
    echo "<p style='color:green'><strong>✓ ImageMagick is loaded</strong></p>";
} else {
    echo "<p><strong>ImageMagick is not loaded</strong></p>";
}

// Intervention Image check
echo "<h2>Intervention Image Check</h2>";
if (class_exists('Intervention\Image\ImageManager')) {
    echo "<p style='color:green'><strong>✓ Intervention\Image\ImageManager class exists</strong></p>";
} else {
    echo "<p style='color:red'><strong>✗ Intervention\Image\ImageManager class not found</strong></p>";
}

if (class_exists('Intervention\Image\Drivers\Gd\Driver')) {
    echo "<p style='color:green'><strong>✓ Intervention\Image\Drivers\Gd\Driver class exists</strong></p>";
} else {
    echo "<p style='color:red'><strong>✗ Intervention\Image\Drivers\Gd\Driver class not found</strong></p>";
} 