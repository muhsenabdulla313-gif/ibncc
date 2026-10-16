<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model
{
    protected $fillable = ['user_id', 'subject', 'body', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];
}