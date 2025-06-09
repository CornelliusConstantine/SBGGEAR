<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MidtransTransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'transaction_id',
        'transaction_status',
        'payment_type',
        'fraud_status',
        'gross_amount',
        'raw_response',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_number', 'order_number');
    }
} 