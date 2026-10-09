<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forane extends Model
{
    protected $fillable = ['diocese_id', 'name'];

    public function diocese()  { return $this->belongsTo(Diocese::class); }
    public function parishes() { return $this->hasMany(Parish::class); }
}
