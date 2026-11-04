<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id', 'variant_id',
        'attribute_option_id',   // added by 2026_11_03_000012 migration (gallery per colour)
        'path', 'alt', 'sort_order', 'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function option()  { return $this->belongsTo(AttributeOption::class, 'attribute_option_id'); }

    public function getUrlAttribute(): string
    {
        return str_starts_with($this->path, 'http') ? $this->path : asset($this->path);
    }
}
