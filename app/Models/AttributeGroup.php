<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
}
