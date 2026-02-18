<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بيانات المستخدم
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // بيانات الملف الشخصي للطالب
            'phone' => ['required', 'string', 'min:6', 'max:20'],
            'country_id' => ['required', 'exists:countries,id'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'professional_status' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.min' => 'يجب ألا يقل الاسم عن 3 أحرف.',
            'name.max' => 'يجب ألا يزيد الاسم عن 255 حرفًا.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.unique' => 'هذا البريد الإلكتروني مسجل بالفعل.',

            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.min' => 'رقم الهاتف قصير جدًا.',
            'phone.max' => 'رقم الهاتف طويل جدًا.',

            'country_id.required' => 'الدولة مطلوبة.',
            'country_id.exists' => 'الدولة المحددة غير صحيحة.',

            'address.required' => 'العنوان مطلوب.',
            'address.min' => 'يجب ألا يقل العنوان عن 5 أحرف.',
            'address.max' => 'يجب ألا يزيد العنوان عن 500 حرف.',

            'gender.required' => 'النوع مطلوب.',
            'gender.in' => 'يجب أن يكون النوع ذكر أو أنثى.',
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
