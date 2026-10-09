<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['type', 'house', 'country_id', 'state_id', 'district_id', 'pin'];

    public function user()     { return $this->belongsTo(User::class); }
    public function country()  { return $this->belongsTo(Country::class); }
    public function state()    { return $this->belongsTo(State::class); }
    public function district() { return $this->belongsTo(District::class); }
}
