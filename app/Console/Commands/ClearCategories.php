<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ClearCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all categories from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing all categories...');
        
        // Check if there are any products using these categories
        $productsCount = DB::table('products')->count();
        if ($productsCount > 0) {
            if (!$this->confirm('There are products in the database that may be linked to categories. Continuing will remove all categories. Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::FAILURE;
            }
        }
        
        try {
            // Force delete all categories (including soft deleted ones)
            Category::withTrashed()->forceDelete();
            
            // Reset the sequence for PostgreSQL
            $connection = config('database.default');
            if ($connection === 'pgsql') {
                DB::statement('ALTER SEQUENCE categories_id_seq RESTART WITH 1');
            }
            
            $this->info('All categories have been removed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to clear categories: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 