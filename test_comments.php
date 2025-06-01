<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\ProductComment;
use Illuminate\Support\Facades\DB;

// Get a product with comments
$product = Product::with(['comments.replies', 'comments.user'])->first();

if (!$product) {
    echo "No products found\n";
    exit;
}

echo "Product: {$product->name} (ID: {$product->id})\n";
echo "Comments count: " . $product->comments->count() . "\n\n";

// Display comments
foreach ($product->comments as $comment) {
    echo "Comment ID: {$comment->id}\n";
    echo "User ID: {$comment->user_id}\n";
    echo "User Name: {$comment->user_name}\n";
    echo "Question: {$comment->question}\n";
    echo "Created at: {$comment->created_at}\n\n";
}

// Check if there are comments without user_id
$commentsWithoutUserId = ProductComment::whereNull('user_id')->count();
echo "Comments without user_id: {$commentsWithoutUserId}\n";

// Check if there's a problem with the data structure
echo "Checking for potential issues...\n";

// Check if there are comments with non-existent user_id
$commentsWithInvalidUserId = DB::select("
    SELECT COUNT(*) as count 
    FROM product_comments pc 
    LEFT JOIN users u ON pc.user_id = u.id 
    WHERE pc.user_id IS NOT NULL AND u.id IS NULL
");
if ($commentsWithInvalidUserId[0]->count > 0) {
    echo "WARNING: Found {$commentsWithInvalidUserId[0]->count} comments with invalid user_id\n";
}

echo "Done.\n"; 