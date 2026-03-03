<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallSession extends Model
{
    protected $guarded = [];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    // ✅ يجب إضافة علاقة الطالب أيضاً لأنك ستحتاجها
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
