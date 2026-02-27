<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $package = $this->route('package');

        return [

            /* =======================
               Package Name
            ======================== */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('packages', 'name')->ignore($package?->id),
            ],

            /* =======================
               Price
            ======================== */
            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            /* =======================
               Discount
            ======================== */
            'discount' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            /* =======================
               Base Minutes
            ======================== */
            'base_minutes' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],

            /* =======================
               Bonus Minutes
            ======================== */
            'bonus_minutes' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            /* =======================
               Validity Days
            ======================== */
            'validity_days' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],

            /* =======================
               Description
            ======================== */
            'description' => [
                'sometimes',
                'required',
                'string',
            ],

            /* =======================
               Status
            ======================== */
            'status' => [
                'sometimes',
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال اسم الباقة.',
            'name.unique'   => 'اسم الباقة مستخدم مسبقًا.',

            'price.required' => 'يرجى إدخال سعر الباقة.',
            'price.numeric'  => 'السعر يجب أن يكون رقمًا صحيحًا.',

            'discount.integer' => 'نسبة الخصم يجب أن تكون رقمًا صحيحًا.',
            'discount.max'     => 'نسبة الخصم لا يمكن أن تتجاوز 100٪.',

            'base_minutes.required' => 'يرجى تحديد عدد الدقائق الأساسية.',
            'bonus_minutes.integer' => 'الدقائق الإضافية يجب أن تكون رقمًا صحيحًا.',

            'validity_days.required' => 'يرجى تحديد مدة الصلاحية.',
            'description.required'   => 'يرجى إدخال وصف الباقة.',

            'status.required' => 'يرجى تحديد حالة الباقة.',
            'status.in'       => 'قيمة الحالة غير صحيحة.',
        ];
    }
}
