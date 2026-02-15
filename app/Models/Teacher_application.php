<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher_application extends Model
{
    protected $fillable = [
        'full_name',
        'gender',
        'email',
        'phone',
        'origin_country',
        'residence_location',
        'qualification',
        'languages',
        'experience_years',
        'work_hours',
        'online_experience',
        'internet_quality',
        'tech_skills',
        'ijazas_text',
        'cv_pdf_path',
        'status',
        'profile_photo_path'
    ];

    protected $casts = [
        'languages'   => 'array',
    ];
        public function tracks()
    {
        return $this->belongsToMany(Track::class, 'teacher_application_tracks');
    }
    public function profile()
    {
        return $this->hasOne(Teacher::class, 'teacher_application_id');
    }
}
