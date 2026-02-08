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

            /* البيانات الشخصية */
            'full_name.required' => 'الاسم الكامل مطلوب',
            'full_name.string' => 'يجب أن يكون الاسم نصًا',
            'full_name.min' => 'الاسم يجب ألا يقل عن 5 أحرف',
            'full_name.max' => 'الاسم لا يجب أن يزيد عن 255 حرف',

            'gender.required' => 'يرجى اختيار الجنس',
            'gender.in' => 'قيمة الجنس غير صحيحة',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.max' => 'البريد الإلكتروني طويل جدًا',

            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا',
            'phone.min' => 'رقم الهاتف قصير جدًا',
            'phone.max' => 'رقم الهاتف طويل جدًا',

            'origin_country.required' => 'يرجى اختيار بلد الأصل',
            'origin_country.string' => 'اسم البلد يجب أن يكون نصًا',
            'origin_country.max' => 'اسم البلد طويل جدًا',

            'residence_location.required' => 'مكان الإقامة مطلوب',
            'residence_location.string' => 'مكان الإقامة يجب أن يكون نصًا',
            'residence_location.max' => 'مكان الإقامة طويل جدًا',

            /* الخلفية العلمية */
            'qualification.required' => 'المؤهل العلمي مطلوب',
            'qualification.string' => 'المؤهل يجب أن يكون نصًا',
            'qualification.max' => 'المؤهل طويل جدًا',

            'tracks.required' => 'يجب اختيار مسار واحد على الأقل',
            'tracks.array' => 'المسارات يجب أن تكون مصفوفة',
            'tracks.min' => 'يجب اختيار مسار واحد على الأقل',
            'tracks.*.exists' => 'أحد المسارات المختارة غير موجود',

            'languages.required' => 'يجب اختيار لغة واحدة على الأقل',
            'languages.array' => 'اللغات يجب أن تكون مصفوفة',
            'languages.min' => 'يجب اختيار لغة واحدة على الأقل',
            'languages.*.in' => 'لغة غير مدعومة',

            /* الخبرة */
            'experience_years.required' => 'عدد سنوات الخبرة مطلوب',
            'experience_years.integer' => 'عدد السنوات يجب أن يكون رقمًا صحيحًا',
            'experience_years.min' => 'عدد السنوات لا يمكن أن يكون سالبًا',
            'experience_years.max' => 'عدد السنوات كبير جدًا',

            'work_hours.required' => 'عدد ساعات العمل مطلوب',
            'work_hours.integer' => 'عدد ساعات العمل يجب أن يكون رقمًا صحيحًا',
            'work_hours.min' => 'عدد ساعات العمل لا يمكن أن يكون أقل من 1',
            'work_hours.max' => 'عدد ساعات العمل لا يمكن أن يتجاوز 16 ساعة',

            'online_experience.required' => 'يرجى تحديد مستوى الخبرة في التعليم عن بعد',
            'online_experience.in' => 'قيمة الخبرة غير صحيحة',

            'internet_quality.required' => 'يرجى تحديد جودة الإنترنت',
            'internet_quality.in' => 'قيمة جودة الإنترنت غير صحيحة',

            'tech_skills.required' => 'يرجى تحديد مستوى المهارات التقنية',
            'tech_skills.in' => 'قيمة المهارات التقنية غير صحيحة',

            /* المرفقات */
            'ijazas_text.required' => 'يرجى كتابة الإجازات الحاصل عليها',
            'ijazas_text.string' => 'الإجازات يجب أن تكون نصًا',
            'ijazas_text.max' => 'الإجازات طويلة جدًا',

            'cv_pdf.required' => 'يرجى رفع ملف السيرة الذاتية',
            'cv_pdf.file' => 'الملف غير صالح',
            'cv_pdf.mimes' => 'يجب أن يكون الملف بصيغة PDF فقط',
            'cv_pdf.max' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ];
    }
}
