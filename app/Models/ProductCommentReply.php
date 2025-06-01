<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCommentReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment_id',
        'user_id',
        'reply',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    protected $appends = ['user_name'];

    public function comment()
    {
        return $this->belongsTo(ProductComment::class, 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'Anonymous';
    }
} 