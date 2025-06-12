<?php

namespace App\Console\Commands;

use App\Http\Controllers\CategoryImageHelper;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessCategoryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:process-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses dan menyimpan gambar kategori dari folder sumber';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pemrosesan gambar kategori...');
        
        // Source directory
        $sourceDir = 'C:\\Users\\Lenovo\\Pictures\\test';
        $sourceDir2 = 'C:\\Users\\Lenovo\\Pictures\\sopdep';
        
        // Get all image files
        $files = $this->getImageFiles($sourceDir);
        $files2 = $this->getImageFiles($sourceDir2);
        
        $allFiles = array_merge($files, $files2);
        
        if (empty($allFiles)) {
            $this->warn('Tidak ada file gambar yang ditemukan di direktori sumber');
            return;
        }
        
        $this->info('Ditemukan ' . count($allFiles) . ' file gambar');
        
        // Get all categories
        $categories = Category::all();
        
        // Process each image
        foreach ($allFiles as $file) {
            $fileName = strtolower(pathinfo($file, PATHINFO_FILENAME));
            
            // Find matching category
            $matchedCategory = null;
            foreach ($categories as $category) {
                $slug = strtolower(str_replace('-', '', $category->slug));
                $name = strtolower(str_replace(' ', '', $category->name));
                
                if (strpos($fileName, $slug) !== false || strpos($fileName, $name) !== false) {
                    $matchedCategory = $category;
                    break;
                }
            }
            
            if ($matchedCategory) {
                try {
                    // Process and save image
                    $images = CategoryImageHelper::processAndSaveImage($file, $matchedCategory->slug);
                    
                    $this->info("Berhasil memproses gambar untuk kategori '{$matchedCategory->name}': {$images['original']}");
                    
                    // Update category icon
                    DB::table('categories')
                        ->where('id', $matchedCategory->id)
                        ->update([
                            'icon' => $images['original']
                        ]);
                } catch (\Exception $e) {
                    $this->error("Gagal memproses gambar {$file}: " . $e->getMessage());
                }
            } else {
                $this->warn("Tidak ada kategori yang cocok untuk gambar: {$file}");
            }
        }
        
        $this->info('Pemrosesan gambar kategori selesai');
    }
    
    /**
     * Get all image files from a directory
     *
     * @param string $dir Directory path
     * @return array
     */
    private function getImageFiles($dir)
    {
        if (!is_dir($dir)) {
            return [];
        }
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $files = [];
        
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $allowedExtensions)) {
                $files[] = $dir . DIRECTORY_SEPARATOR . $file;
            }
        }
        
        return $files;
    }
} 