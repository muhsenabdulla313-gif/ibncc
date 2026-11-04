<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'rating', 'title', 'comment', 'is_verified_purchase', 'status',
    ];

    protected function casts(): array
    {
        return [
            'rating'               => 'integer',
            'is_verified_purchase' => 'boolean',
        ];
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function user()    { return $this->belongsTo(User::class); }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }
}
