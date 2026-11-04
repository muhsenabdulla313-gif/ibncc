<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'seller_id',
        'seller_name', 'seller_phone',
        'title', 'sku', 'options', 'unit_name', 'hsn_code', 'price_type',
        'qty', 'unit_price', 'tax_rate', 'tax_amount', 'line_total',
        'status', 'seller_note', 'contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'options'      => 'array',
            'qty'          => 'decimal:3',
            'unit_price'   => 'decimal:4',
            'tax_rate'     => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'line_total'   => 'decimal:2',
            'contacted_at' => 'datetime',
        ];
    }

    public function order()   { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function seller()  { return $this->belongsTo(User::class, 'seller_id'); }
}
