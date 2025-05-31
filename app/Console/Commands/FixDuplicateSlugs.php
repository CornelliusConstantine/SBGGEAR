<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Product;

class FixDuplicateSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicate slugs in the products table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Looking for duplicate slugs...');
        
        // Find duplicates using PostgreSQL compatible syntax
        $duplicates = DB::select(
            "SELECT slug FROM products GROUP BY slug HAVING COUNT(*) > 1"
        );
        
        if (count($duplicates) === 0) {
            $this->info('No duplicate slugs found.');
            return 0;
        }
        
        $this->info('Found ' . count($duplicates) . ' duplicate slugs. Fixing...');
        
        $fixed = 0;
        foreach ($duplicates as $duplicate) {
            // Get all products with this slug except the first one
            $products = Product::where('slug', $duplicate->slug)
                ->orderBy('created_at')
                ->get();
            
            $this->line('Fixing slug: ' . $duplicate->slug . ' (' . $products->count() . ' products)');
            
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
                
                $this->line('  - Updating product ID ' . $product->id . ' from "' . $product->slug . '" to "' . $newSlug . '"');
                
                $product->slug = $newSlug;
                $product->save();
                $fixed++;
            }
        }
        
        $this->info('Fixed ' . $fixed . ' duplicate slugs.');
        
        return 0;
    }
} 