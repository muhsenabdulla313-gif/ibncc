<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;


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
protected static function booted(): void
{
    static::creating(function (User $user) {
        $user->uuid ??= (string) Str::uuid();      // every new user gets a uuid
    });
}

public function getRouteKeyName(): string
{
    return 'uuid';                                  // route('members.show', $user) now builds a uuid URL
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
    public function savedProfiles()
{
    return $this->belongsToMany(User::class, 'saved_profiles', 'user_id', 'saved_user_id')->withTimestamps();
}

public function blockedUsers()
{
    return $this->belongsToMany(User::class, 'blocked_profiles', 'user_id', 'blocked_user_id')->withTimestamps();
}

/** ids blocked by me OR who blocked me */
public function blockedIds(): array
{
    return DB::table('blocked_profiles')->where('user_id', $this->id)->pluck('blocked_user_id')
        ->merge(DB::table('blocked_profiles')->where('blocked_user_id', $this->id)->pluck('user_id'))
        ->map(fn ($i) => (int) $i)->unique()->values()->all();
}

public function hasBlocked(User $u): bool
{
    return DB::table('blocked_profiles')->where('user_id', $this->id)->where('blocked_user_id', $u->id)->exists();
}
/** Share list: saved profiles FIRST, then everyone else A-Z */
public static function shareCandidates(User $me, ?string $q = null, int $limit = 20)
{
    return static::query()
        ->with(['profile.job', 'presentAddress.district'])
        ->whereKeyNot($me->id)
        ->whereNotIn('id', $me->blockedIds())
        ->when($q, fn ($x) => $x->where('name', 'like', '%' . $q . '%'))
        ->orderByRaw('EXISTS (SELECT 1 FROM saved_profiles sp WHERE sp.user_id = ? AND sp.saved_user_id = users.id) DESC', [$me->id])
        ->orderBy('name')
        ->limit($limit)
        ->get();
}

}