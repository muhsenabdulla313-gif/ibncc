<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    protected $fillable = ['forane_id', 'name'];

    public function forane() { return $this->belongsTo(Forane::class); }
    public function users()  { return $this->hasMany(User::class); }
}
