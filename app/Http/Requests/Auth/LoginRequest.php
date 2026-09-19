<?php

namespace AppHttpRequestsAuth;

use IlluminateFoundationHttpFormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ];
    }
}
