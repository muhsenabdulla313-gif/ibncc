<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDocument extends Model
{
    protected $fillable = ['product_id', 'title', 'path', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function product() { return $this->belongsTo(Product::class); }
}
