<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'category_id', 'brand_id', 'unit_id', 'stock_unit_id',
        'title', 'slug', 'model_no', 'short_description', 'description',
        'sale_type', 'pack_qty', 'min_order_qty', 'max_order_qty', 'track_stock',
        'hsn_code', 'tax_rate', 'price_includes_tax', 'offer_text',
        'is_featured', 'is_assured', 'rating_avg', 'rating_count',
        'status', 'rejection_reason', 'published_at', 'meta_title', 'meta_description',
        // listing columns (added by 2026_11_03_000012 migration)
        'qty_step', 'min_price', 'max_price', 'in_stock', 'specs_line', 'badge', 'discount_label',
    ];

    protected function casts(): array
    {
        return [
            'pack_qty'           => 'decimal:3',
            'min_order_qty'      => 'decimal:3',
            'max_order_qty'      => 'decimal:3',
            'qty_step'           => 'decimal:3',
            'min_price'          => 'decimal:2',
            'max_price'          => 'decimal:2',
            'tax_rate'           => 'decimal:2',
            'rating_avg'         => 'decimal:2',
            'rating_count'       => 'integer',
            'track_stock'        => 'boolean',
            'in_stock'           => 'boolean',
            'price_includes_tax' => 'boolean',
            'is_featured'        => 'boolean',
            'is_assured'         => 'boolean',
            'published_at'       => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $p) {
            $p->uuid ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------- relations ---------- */

    public function seller()       { return $this->belongsTo(User::class, 'user_id'); }
    public function category()     { return $this->belongsTo(Category::class); }
    public function brand()        { return $this->belongsTo(Brand::class); }
    public function unit()         { return $this->belongsTo(Unit::class); }
    public function stockUnit()    { return $this->belongsTo(Unit::class, 'stock_unit_id'); }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_values')
            ->withPivot(['attribute_option_id', 'value_text', 'value_number', 'value_bool', 'value_date'])
            ->withTimestamps();
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_qty');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function videos()    { return $this->hasMany(ProductVideo::class)->orderBy('sort_order'); }
    public function documents() { return $this->hasMany(ProductDocument::class)->orderBy('sort_order'); }
    public function tags()      { return $this->belongsToMany(Tag::class, 'product_tag'); }
    public function reviews()   { return $this->hasMany(ProductReview::class); }

    // stock_movements has variant_id (not product_id) -> go through variants
    public function stockMovements()
    {
        return $this->hasManyThrough(StockMovement::class, ProductVariant::class, 'product_id', 'variant_id');
    }

    /* ---------- scopes ---------- */

    public function scopePublished($q)
    {
        return $q->where('status', 'active')->whereNotNull('published_at');
    }

    public function scopeRetail($q)
    {
        return $q->whereIn('sale_type', ['retail', 'both']);
    }

    public function scopeTrading($q)
    {
        return $q->whereIn('sale_type', ['wholesale', 'both']);
    }
}
