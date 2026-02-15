<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher_application_track extends Model
{
    protected $fillable = [
        'teacher_application_id',
        'track_id',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    public function teacherApplication()
    {
        return $this->belongsTo(Teacher_application::class, 'teacher_application_id');
    }
    // app/Models/Teacher_application.php
public function tracks()
{
    return $this->belongsToMany(Track::class, 'teacher_application_tracks', 'teacher_application_id', 'track_id');
}
}
