<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country_id',
        'phone',
        'address',
        'qualification',
        'professional_status',
        'gender',
        'profile_photo_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function favorites()
    {
        return $this->belongsToMany(Teacher_application::class, 'favorites', 'student_id', 'teacher_id')
            ->withTimestamps();
    }
    
}
