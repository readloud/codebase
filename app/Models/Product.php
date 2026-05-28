<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'description', 'category_id', 'brand_id',
        'vendor_product_id', 'vendor', 'buy_price', 'sell_price',
        'stock', 'image_url', 'metadata', 'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}