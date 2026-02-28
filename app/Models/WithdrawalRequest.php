<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'teacher_id',
        'amount',
        'account_number',
        'notes',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
