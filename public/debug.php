<?php
// Debug script to help diagnose routing issues

header('Content-Type: text/plain');

echo "=== REQUEST INFO ===\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "\n";

echo "=== PARSED URI ===\n";
$uri = parse_url($_SERVER['REQUEST_URI']);
echo "Path: " . $uri['path'] . "\n";
echo "Query: " . (isset($uri['query']) ? $uri['query'] : 'none') . "\n";
echo "Fragment: " . (isset($uri['fragment']) ? $uri['fragment'] : 'none') . "\n";
echo "\n";

echo "=== FILE CHECK ===\n";
$requestedFile = __DIR__ . $uri['path'];
echo "Requested file path: " . $requestedFile . "\n";
echo "File exists: " . (file_exists($requestedFile) ? 'Yes' : 'No') . "\n";
echo "Is directory: " . (is_dir($requestedFile) ? 'Yes' : 'No') . "\n";
echo "\n";

echo "=== SERVER SOFTWARE ===\n";
echo $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "\n";

echo "=== HTACCESS CHECK ===\n";
echo "Public .htaccess exists: " . (file_exists(__DIR__ . '/.htaccess') ? 'Yes' : 'No') . "\n";
echo "Root .htaccess exists: " . (file_exists(__DIR__ . '/../.htaccess') ? 'Yes' : 'No') . "\n";
echo "\n";

echo "=== DIRECTORY LISTING ===\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    echo $file . (is_dir(__DIR__ . '/' . $file) ? '/' : '') . "\n";
} 