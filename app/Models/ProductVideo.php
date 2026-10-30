<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVideo extends Model
{
    
    protected $fillable = [
        'product_id',
        'video_path',
        'video_url',
        'title',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
