<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
     protected $fillable = [
        'attribute_group_id',
        'name',
        'slug',
        'type',
        'is_filterable',
        'is_variant',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_variant' => 'boolean',
        ];
    }

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class);
    }

    public function categories()
{
    return $this->belongsToMany(
        Category::class,
        'category_attributes',
        'attribute_id',
        'category_id'
    )->withPivot('applies_to');
}
    public function productValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
