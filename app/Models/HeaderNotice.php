<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderNotice extends Model
{
     protected $fillable = ['text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
