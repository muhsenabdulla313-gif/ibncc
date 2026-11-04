<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'variant_id', 'user_id', 'type', 'qty_change', 'qty_after', 'note',
        'reference_type', 'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'qty_change' => 'decimal:3',
            'qty_after'  => 'decimal:3',
        ];
    }

    public function variant()   { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function user()      { return $this->belongsTo(User::class); }
    public function reference() { return $this->morphTo(); }
}
