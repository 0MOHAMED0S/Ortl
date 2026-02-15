<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /* ================== الصورة الشخصية ================== */
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // بحد أقصى 2 ميجا

            /* ================== البيانات الشخصية ================== */
            'full_name' => ['required', 'string', 'min:5', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'origin_country' => ['required', 'string', 'max:100'],
            'residence_location' => ['required', 'string', 'max:255'],

            /* ================== الخلفية العلمية ================== */
            'qualification' => ['required', 'string', 'max:255'],
            'tracks' => ['required', 'array', 'min:1'],
            'tracks.*' => ['exists:tracks,id'],

            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => [
                'required',
                Rule::in([
                    'arabic',
                    'english',
                    'french',
                    'urdu',
                    'indonesian',
                    'turkish',
                    'spanish',
                    'german',
                ]),
            ],

            /* ================== الخبرة ================== */
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'work_hours' => ['required', 'integer', 'min:1', 'max:16'],
            'online_experience' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
            'internet_quality' => ['required', Rule::in(['weak', 'acceptable', 'good', 'excellent'])],
            'tech_skills' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],

            /* ================== المرفقات ================== */
            'ijazas_text' => ['required', 'string', 'max:2000'],
            'cv_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            /* الصورة الشخصية */
            'profile_photo.required' => 'الصورة الشخصية مطلوبة',
            'profile_photo.image' => 'يجب أن يكون الملف المرفوع صورة',
            'profile_photo.mimes' => 'يجب أن تكون الصورة بصيغة: jpeg, png, jpg',
            'profile_photo.max' => 'حجم الصورة لا يجب أن يتجاوز 2 ميجابايت',

            /* البيانات الشخصية */
            'full_name.required' => 'الاسم الكامل مطلوب',
            'full_name.min' => 'الاسم يجب ألا يقل عن 5 أحرف',
            'gender.required' => 'يرجى اختيار الجنس',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'phone.required' => 'رقم الهاتف مطلوب',
            'origin_country.required' => 'يرجى اختيار بلد الأصل',
            'residence_location.required' => 'مكان الإقامة مطلوب',

            /* الخلفية العلمية */
            'qualification.required' => 'المؤهل العلمي مطلوب',
            'tracks.required' => 'يجب اختيار مسار واحد على الأقل',
            'tracks.*.exists' => 'أحد المسارات المختارة غير موجود',
            'languages.required' => 'يجب اختيار لغة واحدة على الأقل',
            'languages.*.in' => 'لغة غير مدعومة',

            /* الخبرة */
            'experience_years.required' => 'عدد سنوات الخبرة مطلوب',
            'work_hours.required' => 'عدد ساعات العمل مطلوب',
            'online_experience.required' => 'يرجى تحديد مستوى الخبرة في التعليم عن بعد',
            'internet_quality.required' => 'يرجى تحديد جودة الإنترنت',
            'tech_skills.required' => 'يرجى تحديد مستوى المهارات التقنية',

            /* المرفقات */
            'ijazas_text.required' => 'يرجى كتابة الإجازات الحاصل عليها',
            'cv_pdf.required' => 'يرجى رفع ملف السيرة الذاتية',
            'cv_pdf.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط',
            'cv_pdf.max' => 'حجم ملف السيرة الذاتية يجب ألا يتجاوز 10 ميجابايت',
        ];
    }
}
