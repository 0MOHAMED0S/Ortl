<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'percent',
        'limit',
        'used',
        'status',
        'expiry_date',
    ];

    // Ensure dates are Carbon instances
    protected $casts = [
        'expiry_date' => 'date',
    ];

    // Helper to check if coupon is valid
    public function isValid()
    {
        return $this->status === 'active' &&
               $this->used < $this->limit &&
               $this->expiry_date->isFuture();
    }
}
