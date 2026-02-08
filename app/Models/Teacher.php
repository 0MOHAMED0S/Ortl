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


    // Belongs to an Application (Form info)
    public function application()
    {
        return $this->belongsTo(Teacher_application::class, 'teacher_application_id');
    }
}
