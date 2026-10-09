<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $fillable = ['job_category_id', 'name'];

    public function category() { return $this->belongsTo(JobCategory::class, 'job_category_id'); }
}
