<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;
 
    protected $fillable = [
        'role_id', 'name', 'email', 'password',
        'phone', 'phone_verified_at', 'parish', 'diocese', 'rite',
        'otp_code', 'otp_expires_at',
    ];
 
    protected $hidden = ['password', 'remember_token', 'otp_code'];
 
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
 
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
 
    public function isAdmin(): bool
    {
        return $this->role->name === 'admin';
    }
 
    public function isMember(): bool
    {
        return $this->role->name === 'member';
    }
 
    public function isGuest(): bool
    {
        return $this->role->name === 'guest';
    }
}
