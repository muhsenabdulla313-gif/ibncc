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

    /**
     * Resolve all images specifically belonging to this variant.
     * Matches direct variant_id, color/variant axis attribute_option_id, or options JSON.
     */
    public function getVariantImagesAttribute()
    {
        // 1. Direct variant_id on product_images
        $direct = ProductImage::where('variant_id', $this->id)->orderBy('sort_order')->get();
        if ($direct->isNotEmpty()) {
            return $direct;
        }

        $this->loadMissing(['attributeValues.attribute', 'attributeValues.option', 'product.images.option']);

        // 2. Prioritize color axis or variant axis attribute_option_id
        $colorVal = $this->attributeValues->first(function ($av) {
            return $av->attribute && ($av->attribute->input_type === 'color' || strtolower($av->attribute->name) === 'color');
        });
        if ($colorVal && $colorVal->attribute_option_id && $this->product) {
            $byColor = $this->product->images->where('attribute_option_id', $colorVal->attribute_option_id)->values();
            if ($byColor->isNotEmpty()) {
                return $byColor;
            }
        }

        // 3. Any other attribute_option_id in attributeValues
        $optIds = $this->attributeValues->pluck('attribute_option_id')->filter()->all();
        if (!empty($optIds) && $this->product) {
            $byOpt = $this->product->images->whereIn('attribute_option_id', $optIds)->values();
            if ($byOpt->isNotEmpty()) {
                return $byOpt;
            }
        }

        // 4. Match from options JSON (e.g. {"color": "Red"})
        if (!empty($this->options) && is_array($this->options) && $this->product) {
            $colorKey = collect(array_keys($this->options))->first(fn($k) => strtolower($k) === 'color');
            if ($colorKey) {
                $colorName = strtolower(trim($this->options[$colorKey]));
                $byColorName = $this->product->images->filter(function ($img) use ($colorName) {
                    return $img->option && strtolower(trim($img->option->label)) === $colorName;
                })->values();
                if ($byColorName->isNotEmpty()) {
                    return $byColorName;
                }
            }

            $labels = array_map('strtolower', array_map('trim', array_values($this->options)));
            $byLabel = $this->product->images->filter(function ($img) use ($labels) {
                return $img->option && in_array(strtolower(trim($img->option->label)), $labels);
            })->values();
            if ($byLabel->isNotEmpty()) {
                return $byLabel;
            }
        }

        return collect();
    }

    /**
     * Primary image for this variant, falling back to product's first image.
     */
    public function getPrimaryImageAttribute(): ?ProductImage
    {
        $variantImgs = $this->variant_images;
        if ($variantImgs->isNotEmpty()) {
            return $variantImgs->firstWhere('is_primary', true) ?? $variantImgs->first();
        }

        return $this->product?->images->firstWhere('is_primary', true) ?? $this->product?->images->first();
    }

    /**
     * URL of the variant's primary image or fallback.
     */
    public function getImageUrlAttribute(): string
    {
        $img = $this->primary_image;
        if ($img) {
            return $img->url;
        }
        return asset('assets/images/no-image.avif');
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
