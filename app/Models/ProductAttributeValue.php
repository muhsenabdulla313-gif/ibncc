<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id', 'attribute_id', 'attribute_option_id',
        'value_text', 'value_number', 'value_bool', 'value_date',
    ];

    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:4',
            'value_bool'   => 'boolean',
            'value_date'   => 'date',
        ];
    }

    public function product()   { return $this->belongsTo(Product::class); }
    public function attribute() { return $this->belongsTo(Attribute::class); }
    public function option()    { return $this->belongsTo(AttributeOption::class, 'attribute_option_id'); }

    /** Single display value whatever the attribute type is. */
    public function getDisplayValueAttribute()
    {
        if ($this->attribute_option_id) return $this->option?->label;
        if (!is_null($this->value_text)) return $this->value_text;
        if (!is_null($this->value_number)) return rtrim(rtrim((string) $this->value_number, '0'), '.');
        if (!is_null($this->value_bool)) return $this->value_bool ? 'Yes' : 'No';
        return $this->value_date?->format('d M Y');
    }
}
