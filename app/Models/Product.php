<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'sku',
        'specifications',
        'images',
        'is_active',
        'weight',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'specifications' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = ['image_url', 'thumbnail_url', 'gallery_urls'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the main image URL for the product
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->images) || !is_array($this->images) || !isset($this->images['main'])) {
            return null;
        }
        return asset('storage/products/original/' . $this->images['main']);
    }

    /**
     * Get the thumbnail URL for the product
     */
    public function getThumbnailUrlAttribute()
    {
        if (empty($this->images) || !is_array($this->images) || !isset($this->images['main'])) {
            return null;
        }
        return asset('storage/products/thumbnails/' . $this->images['main']);
    }

    /**
     * Get gallery image URLs for the product
     */
    public function getGalleryUrlsAttribute()
    {
        if (empty($this->images) || !is_array($this->images) || !isset($this->images['gallery'])) {
            return [];
        }
        
        $urls = [];
        foreach ($this->images['gallery'] as $image) {
            $urls[] = asset('storage/products/original/' . $image);
        }
        
        return $urls;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($product) {
            // Ensure slug is unique
            if ($product->isDirty('slug')) {
                $baseSlug = $product->slug;
                $slug = $baseSlug;
                $counter = 1;
                
                while (static::where('slug', $slug)
                        ->where('id', '!=', $product->id)
                        ->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                
                $product->slug = $slug;
            }
        });
    }
} 