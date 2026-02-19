<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class DeleteDaySlotsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'       => 'يجب تحديد التاريخ المراد حذفه.',
            'date.date'           => 'صيغة التاريخ غير صحيحة.',
            'date.after_or_equal' => 'لا يمكنك حذف مواعيد من الماضي.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'فشل التحقق من البيانات',
            'errors'  => $validator->errors()
        ], 422));
    }
}
