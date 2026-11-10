<?php

namespace App\Http\Requests\Auth;

use App\Support\MobileNumber;
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
            'mobile' => MobileNumber::normalize((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Format and demo-account exceptions are enforced by AuthService,
            // against the persisted user and its expected role.
            'mobile' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }
}
