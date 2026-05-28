<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'product_id', 'customer_phone',
        'customer_email', 'amount', 'status', 'payment_method',
        'payment_reference', 'vendor_order_id', 'vendor_response',
        'retry_count', 'paid_at', 'completed_at'
    ];

    protected $casts = [
        'vendor_response' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}