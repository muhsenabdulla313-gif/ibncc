<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diocese extends Model
{
    protected $fillable = ['rite_id', 'name'];

    public function rite()    { return $this->belongsTo(Rite::class); }
    public function foranes() { return $this->hasMany(Forane::class); }
}
