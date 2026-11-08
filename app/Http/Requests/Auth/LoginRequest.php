<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => \App\Support\MobileNumber::normalize((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }
}
