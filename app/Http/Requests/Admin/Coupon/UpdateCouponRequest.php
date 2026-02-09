<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        // لو جاي بس يغير الحالة → مفيش validation
        if ($this->has('status_toggle')) {
            return [];
        }

        return [
            'code' => 'required|string|max:20|unique:coupons,code,' . $this->route('id'),
            'percent' => 'required|integer|min:1|max:100',
            'limit' => 'required|integer|min:1|max:100000',
            'expiry_date' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'كود الكوبون مطلوب',
            'code.string'   => 'كود الكوبون يجب أن يكون نصًا',
            'code.max'      => 'كود الكوبون لا يزيد عن 20 حرفًا',
            'code.unique'   => 'هذا الكود مستخدم من قبل',

            'percent.required' => 'نسبة الخصم مطلوبة',
            'percent.integer'  => 'نسبة الخصم يجب أن تكون رقمًا صحيحًا',
            'percent.min'      => 'أقل نسبة خصم هي 1%',
            'percent.max'      => 'أقصى نسبة خصم هي 100%',

            'limit.required' => 'عدد مرات الاستخدام مطلوب',
            'limit.integer'  => 'عدد مرات الاستخدام يجب أن يكون رقمًا صحيحًا',
            'limit.min'      => 'عدد مرات الاستخدام يجب أن يكون على الأقل مرة واحدة',
            'limit.max'      => 'عدد مرات الاستخدام كبير جدًا',

            'expiry_date.required' => 'تاريخ الانتهاء مطلوب',
            'expiry_date.date'     => 'صيغة تاريخ الانتهاء غير صحيحة',
            'expiry_date.after'    => 'تاريخ الانتهاء يجب أن يكون في المستقبل',
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
