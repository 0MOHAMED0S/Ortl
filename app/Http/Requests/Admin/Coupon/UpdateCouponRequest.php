<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // إذا كان الطلب لتغيير الحالة فقط، لا نحتاج لقواعد التحقق
        if ($this->has('status_toggle')) {
            return [];
        }

        // جلب معرف الكوبون من المسار (الراوت) أيًا كان اسمه {id} أو {coupon}
        $couponId = $this->route('id') ?? $this->route('coupon');

        return [
            // إضافة استثناء للمعرف الحالي ليتجاهل الكود الخاص به عند التحقق من عدم التكرار
            'code' => 'required|string|max:20|unique:coupons,code,' . $couponId,
            'percent' => 'required|integer|min:1|max:100',
            'limit' => 'required|integer|min:1|max:100000',
            // تم تغيير after:today إلى after_or_equal:today
            // للسماح باختيار تاريخ اليوم إذا لم ينتهي بعد
            'expiry_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الكوبون مطلوب',
            'code.string'   => 'كود الكوبون يجب أن يكون نصًا',
            'code.max'      => 'كود الكوبون لا يزيد عن 20 حرفًا',
            'code.unique'   => 'هذا الكود مستخدم من قبل، اختر كوداً آخر',

            'percent.required' => 'نسبة الخصم مطلوبة',
            'percent.integer'  => 'نسبة الخصم يجب أن تكون رقمًا صحيحًا',
            'percent.min'      => 'أقل نسبة خصم هي 1%',
            'percent.max'      => 'أقصى نسبة خصم هي 100%',

            'limit.required' => 'عدد مرات الاستخدام مطلوب',
            'limit.integer'  => 'عدد مرات الاستخدام يجب أن يكون رقمًا صحيحًا',
            'limit.min'      => 'يجب أن يكون الحد الأدنى مرة واحدة',
            'limit.max'      => 'عدد مرات الاستخدام كبير جدًا',

            'expiry_date.required' => 'تاريخ الانتهاء مطلوب',
            'expiry_date.date'     => 'صيغة تاريخ الانتهاء غير صحيحة',
            'expiry_date.after_or_equal' => 'تاريخ الانتهاء لا يمكن أن يكون في الماضي',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'كود الكوبون',
            'percent' => 'نسبة الخصم',
            'limit' => 'عدد مرات الاستخدام',
            'expiry_date' => 'تاريخ الانتهاء',
        ];
    }
}
