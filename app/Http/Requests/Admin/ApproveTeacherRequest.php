<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveTeacherRequest extends FormRequest
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
            'salary' => [
                'required',
                'numeric',
                'min:0',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            // 👇 التأكد أن الإيميل غير موجود مسبقًا
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'salary.required' => 'الراتب مطلوب',
            'salary.numeric' => 'الراتب يجب أن يكون رقمًا',
            'salary.min' => 'الراتب لا يمكن أن يكون أقل من صفر',

            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف',

            'profile_photo.required' => 'الصورة الشخصية مطلوبة',
            'profile_photo.image' => 'الملف يجب أن يكون صورة',
            'profile_photo.mimes' => 'الصورة يجب أن تكون بصيغة jpeg أو png أو jpg أو gif',
            'profile_photo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل',
        ];
    }
}
