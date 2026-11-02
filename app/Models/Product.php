<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   protected $fillable = [
    'uuid',
    'user_id',
    'category_id',
    'brand_id',
    'unit_id',
    'title',
    'slug',
    'model_no',
    'specs_line',
    'short_description',
    'description',
    'sale_type',
    'pack_qty',
    'min_order_qty',
    'max_order_qty',
    'qty_step',
    'track_stock',
    'min_price',
    'max_price',
    'in_stock',
    'hsn_code',
    'tax_rate',
    'price_includes_tax',
    'offer_text',
    'discount_label',
    'badge',
    'is_featured',
    'is_assured',
    'rating_avg',
    'rating_count',
    'status',
    'rejection_reason',
    'published_at',
    'meta_title',
    'meta_description',
];

   protected function casts(): array
{
    return [
        'pack_qty' => 'decimal:3',
        'min_order_qty' => 'decimal:3',
        'max_order_qty' => 'decimal:3',
        'qty_step' => 'decimal:3',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'track_stock' => 'boolean',
        'in_stock' => 'boolean',
        'tax_rate' => 'decimal:2',
        'price_includes_tax' => 'boolean',
        'is_featured' => 'boolean',
        'is_assured' => 'boolean',
        'rating_avg' => 'decimal:2',
        'rating_count' => 'integer',
        'published_at' => 'datetime',
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
  public function scopePublished($query)
{
    return $query->where('status', 'active')
        ->whereNotNull('published_at');
}

public function scopeRetail($query)
{
    return $query->whereIn('sale_type', ['retail', 'both']);
}

public function scopeTrading($query)
{
    return $query->whereIn('sale_type', ['wholesale', 'both']);
}
public function attributes()
{
    return $this->belongsToMany(
        Attribute::class,
        'product_attribute_values',
        'product_id',
        'attribute_id'
    )->withPivot([
        'attribute_option_id',
        'value_text',
        'value_number',
        'value_bool',
        'value_date',
    ])->withTimestamps();
}
}
