<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
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
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.exists'   => 'هذا البريد غير مسجل لدينا',

            'password.required' => 'كلمة المرور مطلوبة',
            'password.min'      => 'كلمة المرور يجب ألا تقل عن 8 أحرف',
        ];
    }
}
