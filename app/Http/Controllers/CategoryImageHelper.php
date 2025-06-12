<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryImageHelper
{
    /**
     * Menyimpan gambar dan membuat thumbnail
     * 
     * @param string $sourcePath Path ke file sumber
     * @param string $categorySlug Slug kategori untuk nama file
     * @return array Array dengan nama file gambar asli dan thumbnail
     */
    public static function processAndSaveImage($sourcePath, $categorySlug)
    {
        // Generate filename with category slug
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = $categorySlug . '.' . $extension;
        
        // Copy file to storage
        $content = file_get_contents($sourcePath);
        Storage::disk('public')->put('products/original/' . $filename, $content);
        
        // Create thumbnail using GD
        $thumbnail = self::createThumbnail($sourcePath);
        Storage::disk('public')->put('products/thumbnails/' . $filename, $thumbnail);
        
        return [
            'original' => $filename,
            'thumbnail' => $filename
        ];
    }
    
    /**
     * Create a thumbnail using PHP GD
     * 
     * @param string $sourcePath Path to source image
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @return string Binary content of the thumbnail
     */
    public static function createThumbnail($sourcePath, $width = 300, $height = 300)
    {
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new \Exception("Invalid image file: {$sourcePath}");
        }
        
        $mime = $imageInfo['mime'];
        
        // Create source image based on file type
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image type: {$mime}");
        }
        
        if (!$sourceImage) {
            throw new \Exception("Failed to create image from file: {$sourcePath}");
        }
        
        // Get dimensions
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // Calculate thumbnail dimensions while maintaining aspect ratio
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = round($sourceWidth * $ratio);
        $targetHeight = round($sourceHeight * $ratio);
        
        // Create thumbnail image
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        
        // Preserve transparency for PNG images
        if ($mime === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $targetWidth, $targetHeight, $transparent);
        }
        
        // Resize the image
        imagecopyresampled(
            $thumbnail, $sourceImage,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $sourceWidth, $sourceHeight
        );
        
        // Capture the image data
        ob_start();
        imagejpeg($thumbnail, null, 80);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
        
        return $imageData;
    }
} 