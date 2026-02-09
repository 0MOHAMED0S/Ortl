<?php

namespace App\Http\Requests\Admin\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . Auth::id(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'الاسم مطلوب',
            'name.string'    => 'الاسم يجب أن يكون نصًا',
            'name.max'       => 'الاسم لا يزيد عن 255 حرفًا',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.max'      => 'البريد الإلكتروني لا يزيد عن 255 حرفًا',
            'email.unique'   => 'هذا البريد الإلكتروني مستخدم من قبل',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
        ];
    }
}
