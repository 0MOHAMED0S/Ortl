<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class UpdateTeacherProfileRequest extends FormRequest
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
            // بيانات أساسية (نمنع تحديث الإيميل)
            'name'               => ['sometimes', 'string', 'max:255'],
            'phone'              => ['sometimes', 'string', 'max:20'],
            'residence_location' => ['sometimes', 'string', 'max:255'],

            // الملفات
            'profile_photo_path' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB
            'cv_pdf_path'        => ['sometimes', 'file', 'mimes:pdf', 'max:5120'], // 5MB

            // البيانات الأكاديمية والخبرة
            'qualification'      => ['sometimes', 'string', 'max:255'],
            'languages'          => ['sometimes', 'array'],
            'languages.*'        => ['string'],
            'experience_years'   => ['sometimes', 'integer', 'min:0'],
            'work_hours'         => ['sometimes', 'integer', 'min:1'],
            'online_experience'  => ['sometimes', 'in:beginner,intermediate,expert'],
            'internet_quality'   => ['sometimes', 'in:weak,acceptable,good,excellent'],
            'tech_skills'        => ['sometimes', 'in:beginner,intermediate,advanced'],
            'ijazas_text'        => ['sometimes', 'nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'يوجد خطأ في البيانات المدخلة.',
            'errors'  => $validator->errors()
        ], 422));
    }
}
