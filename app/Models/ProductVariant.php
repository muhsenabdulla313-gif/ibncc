<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'options', 'mrp', 'price', 'cost_price',
        'stock', 'reserved_stock', 'low_stock_alert',
        'weight', 'length', 'width', 'height', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'options'         => 'array',
            'mrp'             => 'decimal:2',
            'price'           => 'decimal:2',
            'cost_price'      => 'decimal:2',
            'stock'           => 'decimal:3',
            'reserved_stock'  => 'decimal:3',
            'low_stock_alert' => 'decimal:3',
            'weight'          => 'decimal:3',
            'length'          => 'decimal:2',
            'width'           => 'decimal:2',
            'height'          => 'decimal:2',
            'is_default'      => 'boolean',
            'is_active'       => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(VariantAttributeValue::class, 'variant_id');
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class, 'variant_id')->orderBy('min_qty');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id')->orderBy('sort_order');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public function getAvailableStockAttribute()
    {
        return max(0, (float) $this->stock - (float) $this->reserved_stock);
    }

    public function getDiscountPercentAttribute(): int
    {
        return ($this->mrp > 0 && $this->price < $this->mrp)
            ? (int) round((($this->mrp - $this->price) / $this->mrp) * 100)
            : 0;
    }
}
