<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    protected $fillable = ['path', 'title'];

    public function user() { return $this->belongsTo(User::class); }
}
