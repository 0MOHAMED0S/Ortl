<?php

namespace App\Http\Requests\Admin\Track;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrackRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tracks', 'name')->ignore($this->track->id),
            ],
            // التحقق من الأيقونة الجديدة
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'target_group' => 'nullable|string|max:255',
            'marketing_value' => 'nullable|string|max:255',
            'description' => 'required|string|min:10',
            'status' => 'required|in:active,stopped,pending',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المسار مطلوب',
            'name.unique' => 'اسم المسار مستخدم من قبل',
            'name.max' => 'اسم المسار يجب ألا يزيد عن 255 حرف',

            // رسائل الأيقونة
            'icon.image' => 'الملف المرفوع يجب أن يكون صورة',
            'icon.mimes' => 'يجب أن تكون الصورة من نوع: jpeg, png, jpg, gif, svg',
            'icon.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',

            'target_group.max' => 'الفئة المستهدفة يجب ألا تزيد عن 255 حرف',

            'marketing_value.max' => 'القيمة التسويقية يجب ألا تزيد عن 255 حرف',

            'description.required' => 'وصف المسار مطلوب',
            'description.min' => 'وصف المسار يجب ألا يقل عن 10 أحرف',

            'status.required' => 'حالة المسار مطلوبة',
            'status.in' => 'حالة المسار غير صحيحة',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المسار',
            'icon' => 'أيقونة المسار',
            'target_group' => 'الفئة المستهدفة',
            'marketing_value' => 'القيمة التسويقية',
            'description' => 'وصف المسار',
            'status' => 'حالة المسار',
        ];
    }
}
