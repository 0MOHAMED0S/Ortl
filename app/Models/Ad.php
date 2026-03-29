<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle', // Added for the lower text
        'image',    // Optional if using background colors
        'bg_color', // Added for background color
        'status',
        'coupon_id', // Added for the coupon association
        'link'
    ];
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
