<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTrackingHistory extends Model
{
    use HasFactory;
    
    protected $table = 'order_tracking_history';

    protected $fillable = [
        'order_id',
        'status',
        'description',
        'location',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
} 