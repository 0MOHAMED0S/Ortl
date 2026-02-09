<?php

namespace App\Http\Requests\User\Contact;

use Illuminate\Foundation\Http\FormRequest;

class SendContactEmailRequest extends FormRequest
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
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'الاسم مطلوب',
            'name.string'      => 'الاسم يجب أن يكون نصًا',
            'name.max'         => 'الاسم لا يزيد عن 255 حرفًا',

            'email.required'   => 'البريد الإلكتروني مطلوب',
            'email.email'      => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.max'        => 'البريد الإلكتروني لا يزيد عن 255 حرفًا',

            'subject.required' => 'عنوان الرسالة مطلوب',
            'subject.string'   => 'عنوان الرسالة يجب أن يكون نصًا',
            'subject.max'      => 'عنوان الرسالة لا يزيد عن 255 حرفًا',

            'message.required' => 'محتوى الرسالة مطلوب',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'subject' => 'عنوان الرسالة',
            'message' => 'محتوى الرسالة',
        ];
    }
}
