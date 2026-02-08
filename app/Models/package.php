<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class package extends Model
{
    protected $fillable = [
        'name',
        'price',
        'discount',
        'base_minutes',
        'bonus_minutes',
        'validity_days',
        'description',
        'status',
    ];
}
