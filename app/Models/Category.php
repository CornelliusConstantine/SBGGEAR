<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['icon_url', 'thumbnail_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Accessor untuk mendapatkan URL icon
    public function getIconUrlAttribute()
    {
        if (!$this->icon) {
            return null;
        }

        if (Storage::disk('public')->exists('products/original/' . $this->icon)) {
            return asset('storage/products/original/' . $this->icon);
        }

        return null;
    }

    // Accessor untuk mendapatkan URL thumbnail
    public function getThumbnailUrlAttribute()
    {
        if (!$this->icon) {
            return null;
        }

        if (Storage::disk('public')->exists('products/thumbnails/' . $this->icon)) {
            return asset('storage/products/thumbnails/' . $this->icon);
        }

        return null;
    }
} 