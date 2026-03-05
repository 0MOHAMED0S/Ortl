<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GiftCard extends Model
{
    use HasFactory;

    /**
     * الحقول القابلة للإدخال (Mass Assignment)
     */
    protected $fillable = [
        'sender_id',
        'package_id',
        'minutes',
        'price',
        'recipient_name',
        'occasion',
        'message',
        'coupon_code',
        'transaction_id',
        'payment_status',
        'status',
        'claimed_by_user_id',
        'claimed_at',
    ];

    /**
     * تحويل أنواع البيانات (Casting)
     */
    protected $casts = [
        'price'      => 'decimal:2',
        'minutes'    => 'integer',
        'claimed_at' => 'datetime', // ليتعامل معه لارافيل ككائن Carbon
    ];

    // ==========================================
    // 🔗 العلاقات (Relationships)
    // ==========================================

    /**
     * المُرسل (صاحب الهدية الذي قام بالدفع)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * المُستلم الفعلي (الذي أدخل الكوبون وقبل الهدية)
     */
    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    /**
     * الباقة التي تم إهداؤها
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * طلب الدفع (Order) المرتبط بهذه الهدية
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'gift_card_id');
    }
}
