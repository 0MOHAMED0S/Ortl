<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSlot extends Model
{
    protected $fillable = ['teacher_id', 'date', 'start_time', 'end_time', 'is_booked'];
    public function booking()
    {
        // الموعد له حجز واحد
        return $this->hasOne(SlotBooking::class, 'teacher_slot_id');
    }
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
