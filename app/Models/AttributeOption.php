<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = ['attribute_id', 'label', 'value', 'color_hex', 'image', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
