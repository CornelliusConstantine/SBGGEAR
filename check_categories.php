<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;

echo "Checking categories in the database...\n";
$categories = Category::all();

echo "Found " . $categories->count() . " categories:\n";
foreach ($categories as $category) {
    echo "ID: " . $category->id . " | Name: " . $category->name . " | Active: " . ($category->is_active ? 'Yes' : 'No') . "\n";
} 