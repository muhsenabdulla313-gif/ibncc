<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedProfile extends Model
{
    protected $fillable = ['user_id', 'shared_user_id', 'recipient_id'];

    public function target()
    {
        return $this->belongsTo(User::class, 'shared_user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
