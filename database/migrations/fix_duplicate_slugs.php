<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Find duplicates using PostgreSQL compatible syntax
        $duplicates = DB::select(
            "SELECT slug FROM products GROUP BY slug HAVING COUNT(*) > 1"
        );

        foreach ($duplicates as $duplicate) {
            // Get all products with this slug except the first one
            $products = Product::where('slug', $duplicate->slug)
                ->orderBy('created_at')
                ->get();
            
            // Skip the first one (keep original)
            $first = true;
            foreach ($products as $product) {
                if ($first) {
                    $first = false;
                    continue;
                }
                
                // Update with a new unique slug
                $baseSlug = $product->slug;
                $newSlug = $baseSlug . '-' . Str::random(5);
                
                // Make sure the new slug is unique
                while (Product::where('slug', $newSlug)->exists()) {
                    $newSlug = $baseSlug . '-' . Str::random(5);
                }
                
                $product->slug = $newSlug;
                $product->save();
            }
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Cannot revert this migration
    }
}; 