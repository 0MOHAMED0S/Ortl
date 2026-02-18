<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'min:5',
                'max:255',
                'unique:users,email'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.min'      => 'يجب ألا يقل البريد الإلكتروني عن 5 أحرف.',
            'email.max'      => 'يجب ألا يزيد البريد الإلكتروني عن 255 حرفًا.',
            'email.unique'   => 'هذا البريد الإلكتروني مسجل بالفعل.',
        ];
    }

    /**
     * Override failedValidation to return JSON with status
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'  => false,
            'message' => 'بيانات غير صحيحة.',
            'errors'  => $validator->errors()
        ], 422);

        throw new HttpResponseException($response);
    }
}
