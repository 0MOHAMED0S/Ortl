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
        'status',
        'icon'
    ];

    public function teacherApplications()
    {
        return $this->belongsToMany(
            Teacher_application::class,
            'teacher_application_tracks',
            'track_id',
            'teacher_application_id'
        );
    }

    // 🔥 هذه هي العلاقة المصححة والاحترافية
    public function teachers()
    {
        return $this->belongsToMany(
            Teacher::class,
            'teacher_application_tracks', // الجدول الوسيط (Pivot Table)
            'track_id',                   // المفتاح الأجنبي للمسار في الجدول الوسيط
            'teacher_application_id',     // المفتاح الأجنبي في الجدول الوسيط الذي يربط بالمعلم
            'id',                         // المفتاح المحلي في جدول المسارات (tracks)
            'teacher_application_id'      // المفتاح المحلي في جدول المعلمين (teachers) الذي يطابق الجدول الوسيط
        );
    }
}
