<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
   protected $fillable = [
        'order_number',
        'buyer_id',
        'status',
        'subtotal',
        'shipping_charge',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_charge' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
