<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'parish_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ---------- relations ---------- */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }
    public function profile()
    {
        return $this->hasOne(Profile::class)->withDefault();
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    public function permanentAddress()
    {
        return $this->hasOne(Address::class)->where('type', 'permanent');
    }
    public function presentAddress()
    {
        return $this->hasOne(Address::class)->where('type', 'present');
    }
    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class);
    }
    public function galleryImages()
    {
        return $this->hasMany(GalleryImage::class);
    }

    protected function foraneName(): Attribute
    {
        return Attribute::get(fn() => $this->parish?->forane?->name);
    }

    protected function dioceseName(): Attribute
    {
        return Attribute::get(fn() => $this->parish?->forane?->diocese?->name);
    }

    protected function riteName(): Attribute
    {
        return Attribute::get(fn() => $this->parish?->forane?->diocese?->rite?->name);
    }

    protected function firstName(): Attribute
    {
        return Attribute::get(fn() => Str::words($this->name, 1, ''));
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }
    public function isMember(): bool
    {
        return $this->role?->name === 'member';
    }
    public function isGuest(): bool
    {
        return $this->role?->name === 'guest';
    }


}