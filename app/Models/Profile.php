<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'photo', 'cover', 'about',
        'dob', 'gender', 'blood_group',
        'whatsapp', 'preferred_contact',
        'employment_status', 'organization', 'work_mode', 'experience_years',
        'job_id',
    ];

    protected $casts = ['dob' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function job()  { return $this->belongsTo(Job::class); }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo
            ? asset('storage/' . $this->photo)
            : asset('assets/images/default-user.png'));
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover
            ? asset('storage/' . $this->cover)
            : asset('assets/images/default-cover.jpg'));
    }
}
