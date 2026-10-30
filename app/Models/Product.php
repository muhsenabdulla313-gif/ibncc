<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'slug',
        'sku',
        'description',
        'product_type',
        'sale_type',
        'price',
        'wholesale_price',
        'min_order_quantity',
        'max_order_quantity',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'min_order_quantity' => 'decimal:3',
            'max_order_quantity' => 'decimal:3',
            'stock' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function videos()
    {
        return $this->hasMany(ProductVideo::class);
    }

    public function documents()
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
