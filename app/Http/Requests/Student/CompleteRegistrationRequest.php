<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRegistrationRequest extends FormRequest
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
            // User data
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Student profile
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
            'name.required' => 'Name is required.',
            'name.min' => 'Name must be at least 3 characters.',
            'name.max' => 'Name must not exceed 255 characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',

            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number is too short.',
            'phone.max' => 'Phone number is too long.',

            'country_id.required' => 'Country is required.',
            'country_id.exists' => 'Selected country is invalid.',

            'address.required' => 'Address is required.',
            'address.min' => 'Address must be at least 5 characters.',

            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be male or female.',
        ];
    }
}
