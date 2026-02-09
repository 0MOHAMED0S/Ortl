<?php

namespace App\Http\Requests\Admin\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can restrict this later to admin only
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9]{10,15}$/',
            ],

            'whatsapp' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9]{10,15}$/',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.required'   => 'البريد الإلكتروني مطلوب',
            'email.email'      => 'صيغة البريد الإلكتروني غير صحيحة',

            'phone.required'   => 'رقم الهاتف مطلوب',
            'phone.regex'      => 'رقم الهاتف يجب أن يحتوي على أرقام فقط وبصيغة صحيحة',

            'whatsapp.regex'   => 'رقم واتساب غير صحيح',
        ];
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone'    => $this->normalizePhone($this->phone),
            'whatsapp' => $this->normalizePhone($this->whatsapp),
        ]);
    }

    /**
     * Normalize phone numbers (remove spaces and symbols).
     */
    private function normalizePhone(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        return preg_replace('/[^0-9+]/', '', $number);
    }
}
