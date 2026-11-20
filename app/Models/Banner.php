<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'image', 'link', 'sort_order', 'is_active',
        'eyebrow', 'title', 'subtitle', 'cta_text', 'text_position',
    ];
    protected $casts = ['is_active' => 'boolean'];
}
