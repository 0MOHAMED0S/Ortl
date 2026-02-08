<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
        use HasFactory;
        protected $fillable = [
        'name',
        'code',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'rate_to_usd',
        'phone_code',
    ];
}
