<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'phone' => ['sometimes', 'string', 'min:6', 'max:20'],
            'address' => ['sometimes', 'string', 'min:5', 'max:255'],
            'qualification' => ['sometimes', 'string', 'max:255'],
            'professional_status' => ['sometimes', 'string', 'max:255'],
            'profile_photo' => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'الاسم يجب ألا يقل عن 3 أحرف',
            'name.max' => 'الاسم طويل جدًا',

            'phone.min' => 'رقم الهاتف قصير جدًا',
            'phone.max' => 'رقم الهاتف طويل جدًا',

            'address.min' => 'العنوان قصير جدًا',

            'profile_photo.image' => 'الملف يجب أن يكون صورة',
            'profile_photo.mimes' => 'الصورة يجب أن تكون بصيغة jpeg أو png أو jpg',
            'profile_photo.max' => 'حجم الصورة يجب ألا يزيد عن 2 ميجابايت',
        ];
    }
}
