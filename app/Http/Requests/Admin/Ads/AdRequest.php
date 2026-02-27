<?php

namespace App\Http\Requests\Admin\Ads;

use Illuminate\Foundation\Http\FormRequest;

class AdRequest extends FormRequest
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
     */
    public function rules(): array
    {
        // 1. القواعد الأساسية المشتركة بين الإضافة (POST) والتعديل (PUT/PATCH)
        $rules = [
            'title'     => 'required|string|min:3|max:255',
            'subtitle'  => 'nullable|string|max:255',
            'bg_color'  => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // ✅ إضافة التحقق من الكوبون (اختياري، وإذا وُجد يجب أن يكون مسجلاً في جدول الكوبونات)
            'coupon_id' => 'nullable|exists:coupons,id',
        ];

        // 2. قواعد تطبق فقط في حالة التعديل (Update)
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = 'nullable|in:active,inactive';
        }

        // 3. قواعد تطبق فقط في حالة الإضافة الجديدة (Store)
        /*
        if ($this->isMethod('POST')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        }
        */

        return $rules;
    }

    /**
     * Custom error messages in Arabic.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'العنوان مطلوب',
            'title.string'   => 'العنوان يجب أن يكون نصًا',
            'title.min'      => 'العنوان لا يمكن أن يقل عن 3 أحرف',
            'title.max'      => 'العنوان لا يمكن أن يزيد عن 255 حرفًا',

            'subtitle.string' => 'العنوان الفرعي يجب أن يكون نصًا',
            'subtitle.max'    => 'العنوان الفرعي لا يمكن أن يزيد عن 255 حرفًا',

            'bg_color.string' => 'تنسيق اللون غير صحيح',

            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'الصورة يجب أن تكون من نوع jpeg, png, jpg, gif',
            'image.max'   => 'حجم الصورة لا يمكن أن يتجاوز 2 ميجابايت',

            // ✅ رسالة الخطأ الخاصة بالكوبون
            'coupon_id.exists' => 'الكوبون المحدد غير موجود أو تم حذفه من النظام',

            'status.required' => 'حالة الإعلان مطلوبة',
            'status.in'       => 'الحالة يجب أن تكون إما نشط (active) أو متوقف (inactive)',
        ];
    }
}
