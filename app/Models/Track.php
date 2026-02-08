<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'name',
        'target_group',
        'marketing_value',
        'description',
        'status'
    ];
    public function teacherApplications()
    {
        return $this->belongsToMany(
            Teacher_application::class,
            'teacher_application_tracks'
        );
    }
}
