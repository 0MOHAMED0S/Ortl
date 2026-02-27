<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function slots()
    {
        return $this->hasMany(TeacherSlot::class);
    }
    public function tracks()
    {
        // هنا نقول لارفل: اجلب المسارات المرتبطة بالطلب (application) التابع لهذا المعلم
        return $this->belongsToMany(Track::class, 'teacher_application_tracks', 'teacher_application_id', 'track_id', 'teacher_application_id');
    }

    // Belongs to an Application (Form info)
    public function application()
    {
        // Make sure 'teacher_application_id' exists in your 'teachers' table
        return $this->belongsTo(Teacher_application::class, 'teacher_application_id');
    }
}
