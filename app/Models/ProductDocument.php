<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDocument extends Model
{
     protected $fillable = [
        'product_id',
        'document_path',
        'title',
        'document_type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
