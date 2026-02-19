<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SetAvailabilityRequest extends FormRequest
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
            'date'       => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required'           => 'حقل التاريخ مطلوب.',
            'date.date'               => 'صيغة التاريخ غير صحيحة.',
            'date.after_or_equal'     => 'يجب أن يكون التاريخ اليوم أو تاريخاً مستقبلياً.',
            'start_time.required'     => 'وقت البداية مطلوب.',
            'start_time.date_format'  => 'صيغة الوقت يجب أن تكون (ساعة:دقيقة).',
            'end_time.required'       => 'وقت النهاية مطلوب.',
            'end_time.date_format'    => 'صيغة الوقت يجب أن تكون (ساعة:دقيقة).',
            'end_time.after'          => 'يجب أن يكون وقت النهاية بعد وقت البداية.',
        ];
    }

    // تخصيص استجابة الخطأ لتكون JSON دائماً بما أننا في API
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'خطأ في البيانات المدخلة',
            'errors'  => $validator->errors()
        ], 422));
    }
}
