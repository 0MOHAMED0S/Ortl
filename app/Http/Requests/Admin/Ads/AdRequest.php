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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|min:3|max:255', // نصي من 3 إلى 255 حرف
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB
        ];

        // إضافة validation للـ status فقط عند التحديث
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = 'required|in:active,inactive';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            // Title
            'title.required' => 'العنوان مطلوب',
            'title.string' => 'العنوان يجب أن يكون نصًا',
            'title.min' => 'العنوان لا يمكن أن يقل عن 3 أحرف',
            'title.max' => 'العنوان لا يمكن أن يزيد عن 255 حرفًا',

            // Image
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'الصورة يجب أن تكون من نوع jpeg, png, jpg, gif',
            'image.max' => 'حجم الصورة لا يمكن أن يتجاوز 2MB',

            // Status (update)
            'status.required' => 'حالة الإعلان مطلوبة',
            'status.in' => 'الحالة يجب أن تكون إما active أو inactive',
        ];
    }
}
