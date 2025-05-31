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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'specifications' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
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
            $urls[] = [
                'original' => asset('storage/products/original/' . $image),
                'thumbnail' => asset('storage/products/thumbnails/' . $image)
            ];
        }
        
        return $urls;
    }
} 