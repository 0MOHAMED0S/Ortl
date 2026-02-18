<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PackagePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon' => ['required', 'string', 'exists:coupons,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'coupon.required' => 'كود الخصم مطلوب.',
            'coupon.string'   => 'كود الخصم يجب أن يكون نصًا.',
            'coupon.exists'   => 'كود الخصم غير صحيح.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => 'بيانات غير صحيحة.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
