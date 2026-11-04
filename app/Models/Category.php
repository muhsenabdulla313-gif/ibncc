<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'path', 'depth', 'description', 'image',
        'meta_title', 'meta_description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'depth'      => 'integer',
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'category_attributes')
            ->withPivot(['is_required', 'is_filterable', 'is_variant_axis', 'applies_to', 'sort_order'])
            ->withTimestamps()
            ->orderBy('category_attributes.sort_order');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }
}
