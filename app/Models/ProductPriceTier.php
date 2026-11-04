<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceTier extends Model
{
    // variant_key is a virtual (generated) column: never fill it
    protected $fillable = ['product_id', 'variant_id', 'price_type', 'min_qty', 'max_qty', 'price'];

    protected function casts(): array
    {
        return [
            'min_qty' => 'decimal:3',
            'max_qty' => 'decimal:3',
            'price'   => 'decimal:4',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function scopeWholesale($q) { return $q->where('price_type', 'wholesale'); }
    public function scopeRetail($q)    { return $q->where('price_type', 'retail'); }
}
