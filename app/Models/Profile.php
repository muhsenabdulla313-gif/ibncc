<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
      public const DEFAULT_HIDDEN = ['personal', 'permanent', 'present'];
 
    protected $fillable = [
        'photo', 'cover', 'about', 'status', 'work_description', 'hidden_sections',
        'dob', 'gender', 'blood_group',
        'whatsapp', 'preferred_contact',
        'employment_status', 'organization', 'work_mode', 'experience_years',
        'job_id',
    ];
 
    protected $casts = [
        'dob'             => 'date',
        'hidden_sections' => 'array',
    ];
 
    public function user() { return $this->belongsTo(User::class); }
    public function job()  { return $this->belongsTo(Job::class); }
 
    public function hiddenList(): array
    {
        return $this->hidden_sections ?? self::DEFAULT_HIDDEN;
    }
 
    public function isHidden(string $section): bool
    {
        return in_array($section, $this->hiddenList(), true);
    }
 
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo
            ? asset('storage/' . $this->photo)
            : asset('assets/images/no-image.avif'));
    }
 
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover
            ? asset('storage/' . $this->cover)
            : asset('assets/images/no-image.avif'));
    }
}
