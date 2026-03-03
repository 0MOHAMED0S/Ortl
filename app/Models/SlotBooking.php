<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotBooking extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(TeacherSlot::class, 'teacher_slot_id');
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
