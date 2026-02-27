<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'status',
    ];
    // Country.php
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'country_id');
    }
}
