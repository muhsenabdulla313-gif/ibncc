<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'name', 'short_name', 'type', 'base_unit_id', 'conversion_factor', 'allow_decimal',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:6',
            'allow_decimal'     => 'boolean',
        ];
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
