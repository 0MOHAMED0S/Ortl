<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'remaining_minutes',
        'expires_at',
        'status',
        'is_gift',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    // In UserPackage.php
// public function package()
// {
//     return $this->belongsTo(Package::class, 'package_id');
// }
}
