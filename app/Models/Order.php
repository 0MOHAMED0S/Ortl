<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'package_id',
        'country_id',
        'amount',
        'currency',
        'paymob_order_id',
        'transaction_id',
        'status',
        'coupon_id',
        'is_gift',
        'gift_card_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    public function giftCard()
    {
        return $this->belongsTo(\App\Models\GiftCard::class, 'gift_card_id');
    }
}
