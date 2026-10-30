<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getUrlAttribute(): ?string
    {
        return Event::assetUrl($this->image);
    }
}
