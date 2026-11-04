<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = [
        'attribute_group_id', 'unit_id', 'name', 'slug', 'input_type',
        'is_filterable', 'is_searchable', 'is_comparable', 'is_variant_axis',
        'is_visible', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable'   => 'boolean',
            'is_searchable'   => 'boolean',
            'is_comparable'   => 'boolean',
            'is_variant_axis' => 'boolean',
            'is_visible'      => 'boolean',
            'sort_order'      => 'integer',
        ];
    }

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attributes')
            ->withPivot(['is_required', 'is_filterable', 'is_variant_axis', 'applies_to', 'sort_order'])
            ->withTimestamps();
    }

    public function productValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
    public function attribute()
{
    return $this->belongsTo(Attribute::class, 'attribute_id');
}
}

