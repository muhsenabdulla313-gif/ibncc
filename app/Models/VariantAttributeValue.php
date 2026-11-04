<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantAttributeValue extends Model
{
    protected $fillable = ['variant_id', 'attribute_id', 'attribute_option_id'];

    public function variant()   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function attribute() { return $this->belongsTo(Attribute::class); }
    public function option()    { return $this->belongsTo(AttributeOption::class, 'attribute_option_id'); }
}
