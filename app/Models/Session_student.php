<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session_student extends Model
{
    protected $fillable = [
        'recitation_session_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    public function session()
    {
        return $this->belongsTo(RecitationSession::class, 'recitation_session_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at'   => 'datetime',
    ];
}
