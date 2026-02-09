<?php

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Password;

class UpdatePasswordProfileRequest extends FormRequest
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
            'current_password' => ['required', 'current_password'],
            'new_password'     => ['required', 'confirmed', Password::min(8)->letters()],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'          => 'كلمة المرور الحالية مطلوبة',
            'current_password.current_password'  => 'كلمة المرور الحالية غير صحيحة',

            'new_password.required'  => 'كلمة المرور الجديدة مطلوبة',
            'new_password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'new_password.min'       => 'يجب أن تكون كلمة المرور 8 أحرف على الأقل',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'كلمة المرور الحالية',
            'new_password' => 'كلمة المرور الجديدة',
        ];
    }
}
