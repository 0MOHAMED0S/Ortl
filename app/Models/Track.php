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
// app/Models/Track.php

public function teacherApplications()
{
    return $this->belongsToMany(
        Teacher_application::class,
        'teacher_application_tracks', // تأكد من وجود هذا الجدول في قاعدة البيانات
        'track_id',
        'teacher_application_id'
    );
}

// app/Models/Track.php

public function teachers()
{
    return $this->hasManyThrough(
        Teacher::class,
        Teacher_application::class,
        'id', // مفتاح خارجي في Teacher_application (سيتم تجاهله لأننا نستخدم belongsToMany)
        'teacher_application_id', // مفتاح خارجي في Teachers
        'id', // مفتاح محلي في Track
        'id'  // مفتاح محلي في Teacher_application
    )->whereHas('application.tracks', function($q) {
        $q->where('tracks.id', $this->id);
    });
}
}
