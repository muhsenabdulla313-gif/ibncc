<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no', 'buyer_id', 'buyer_name', 'buyer_phone', 'buyer_email',
        'shipping_address', 'buyer_note', 'tax_total', 'total', 'status',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'tax_total'        => 'decimal:2',
            'total'            => 'decimal:2',
        ];
    }

    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function items() { return $this->hasMany(OrderItem::class); }
}
