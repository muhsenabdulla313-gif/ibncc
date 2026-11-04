<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class)->orderBy('sort_order');
    }
}
