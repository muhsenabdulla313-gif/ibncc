<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'wholesale_price',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(VariantAttributeValue::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class, 'variant_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id')
            ->orderBy('sort_order');
    }
}
