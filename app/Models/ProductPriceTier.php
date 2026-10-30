<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceTier extends Model
{
    protected $fillable = [
        'product_id',
        'variant_id',
        'min_quantity',
        'max_quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'decimal:3',
            'max_quantity' => 'decimal:3',
            'price' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
