<?php

// Display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP INI Fixer for GD Extension\n\n";

// Get PHP ini path
$php_ini_path = php_ini_loaded_file();
echo "PHP INI Path: {$php_ini_path}\n";

// Check if GD is loaded
if (extension_loaded('gd')) {
    echo "GD is already loaded. Version: " . gd_info()['GD Version'] . "\n";
    echo "No need to fix the php.ini file.\n";
} else {
    echo "GD is NOT loaded.\n";
}

echo "\nLoaded extensions:\n";
$extensions = get_loaded_extensions();
sort($extensions);
echo implode(", ", $extensions) . "\n";

// Check if we can find the php.ini file for the web server
echo "\nChecking for different php.ini files:\n";
$possible_paths = [
    'C:\xampp\php\php.ini',
    'C:\php\php.ini',
    'C:\Windows\php.ini',
    'C:\Program Files\PHP\php.ini',
    'C:\Program Files (x86)\PHP\php.ini',
];

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        echo "Found PHP INI at: {$path}\n";
        
        // Check if GD is enabled in this file
        $content = file_get_contents($path);
        if (strpos($content, 'extension=gd') !== false) {
            $gd_line = preg_grep('/extension=gd/', explode("\n", $content));
            $gd_line = reset($gd_line);
            echo "  GD line: " . trim($gd_line) . "\n";
            
            if (strpos($gd_line, ';') === 0) {
                echo "  GD is commented out in this file.\n";
            } else {
                echo "  GD is enabled in this file.\n";
            }
        } else {
            echo "  GD extension not found in this file.\n";
        }
    }
}

// Try to find the PHP extension directory
$extension_dir = ini_get('extension_dir');
echo "\nPHP Extension directory: {$extension_dir}\n";

// Check if the GD DLL exists
$gd_dll = $extension_dir . '/php_gd.dll';
if (file_exists($gd_dll)) {
    echo "GD DLL found at: {$gd_dll}\n";
} else {
    echo "GD DLL not found at: {$gd_dll}\n";
} 