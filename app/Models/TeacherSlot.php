<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSlot extends Model
{
    protected $fillable = ['teacher_id', 'date', 'start_time', 'end_time', 'is_booked'];
}
