<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecitationSession extends Model
{
    protected $fillable = [
        'title',
        'description',
        'teacher_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'max_participants',
        'created_by',
        'channel_name',
        'status',
        'is_recorded',
        'agora_resource_id',
        'agora_sid',
        'recording_url'

    ];

    protected $guarded = [];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_LIVE = 'live';
    const STATUS_ENDED = 'ended';
    // public function teacher()
    // {
    //     return $this->belongsTo(Teacher::class);
    // }
    public function attendees()
{
    // الربط مع موديل حضور الطلاب باستخدام المفتاح الأجنبي الصحيح
    return $this->hasMany(Session_student::class, 'recitation_session_id');
}

    public function teacher()
{
    return $this->belongsTo(Teacher::class, 'teacher_id');
}
    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function students()
    {
        return $this->hasMany(Session_student::class, 'recitation_session_id');
    }
}
