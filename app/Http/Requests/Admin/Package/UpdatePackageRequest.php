<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $packageId = $this->route('package')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:packages,name,' . $packageId,
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'discount' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'base_minutes' => [
                'required',
                'integer',
                'min:1',
            ],

            'bonus_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'validity_days' => [
                'required',
                'integer',
                'min:1',
            ],

            'description' => [
                'required',
                'string',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            /* =======================
               Package Name
            ======================== */
            'name.required' => 'اسم الباقة مطلوب',
            'name.string'   => 'اسم الباقة يجب أن يكون نصًا',
            'name.max'      => 'اسم الباقة لا يجب أن يتجاوز 255 حرفًا',
            'name.unique'   => 'اسم الباقة مستخدم بالفعل، الرجاء اختيار اسم آخر',

            /* =======================
               Price
            ======================== */
            'price.required' => 'سعر الباقة مطلوب',
            'price.numeric'  => 'سعر الباقة يجب أن يكون رقمًا',
            'price.min'      => 'سعر الباقة لا يمكن أن يكون أقل من صفر',

            /* =======================
               Discount
            ======================== */
            'discount.integer' => 'نسبة الخصم يجب أن تكون رقمًا صحيحًا',
            'discount.min'     => 'نسبة الخصم لا يمكن أن تكون أقل من 0%',
            'discount.max'     => 'نسبة الخصم لا يمكن أن تتجاوز 100%',

            /* =======================
               Base Minutes
            ======================== */
            'base_minutes.required' => 'عدد الدقائق الأساسية مطلوب',
            'base_minutes.integer'  => 'عدد الدقائق الأساسية يجب أن يكون رقمًا صحيحًا',
            'base_minutes.min'      => 'عدد الدقائق الأساسية يجب أن يكون دقيقة واحدة على الأقل',

            /* =======================
               Bonus Minutes
            ======================== */
            'bonus_minutes.integer' => 'الدقائق الإضافية يجب أن تكون رقمًا صحيحًا',
            'bonus_minutes.min'     => 'الدقائق الإضافية لا يمكن أن تكون أقل من صفر',

            /* =======================
               Validity Days
            ======================== */
            'validity_days.required' => 'مدة صلاحية الباقة مطلوبة',
            'validity_days.integer'  => 'مدة الصلاحية يجب أن تكون رقمًا صحيحًا',
            'validity_days.min'      => 'مدة الصلاحية يجب أن تكون يومًا واحدًا على الأقل',

            /* =======================
               Description
            ======================== */
            'description.required' => 'وصف الباقة مطلوب',
            'description.string'   => 'وصف الباقة يجب أن يكون نصًا',

            /* =======================
               Status
            ======================== */
            'status.required' => 'حالة الباقة مطلوبة',
            'status.in'       => 'حالة الباقة يجب أن تكون إما نشطة أو غير نشطة',
        ];
    }
}
