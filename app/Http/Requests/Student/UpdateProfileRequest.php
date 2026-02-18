<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'name.min' => 'الاسم يجب ألا يقل عن 3 أحرف.',
            'name.max' => 'الاسم طويل جدًا.',

            'phone.min' => 'رقم الهاتف قصير جدًا.',
            'phone.max' => 'رقم الهاتف طويل جدًا.',

            'address.min' => 'العنوان قصير جدًا.',

            'profile_photo.image' => 'الملف يجب أن يكون صورة.',
            'profile_photo.mimes' => 'الصورة يجب أن تكون بصيغة jpeg أو png أو jpg أو gif.',
            'profile_photo.max' => 'حجم الصورة يجب ألا يزيد عن 2 ميجابايت.',
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
