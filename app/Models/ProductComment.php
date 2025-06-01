<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'question',
    ];

    protected $casts = [];

    protected $appends = ['user_name'];

    protected $with = ['replies']; // Always load replies with comments

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(ProductCommentReply::class, 'comment_id')->orderBy('created_at', 'asc');
    }

    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'Anonymous';
    }
} 