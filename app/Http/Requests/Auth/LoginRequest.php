<?php

namespace App\\Http\\Requests\\Auth;

use App\\Support\\MobileNumber;
use Illuminate\\Foundation\\Http\\FormRequest;

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
            'mobile' => [
                'required',
                function (string $attribute, mixed $value, \\Closure $fail): void {
                    $mobile = MobileNumber::normalize((string) $value);
                    $isValidStandardMobile = MobileNumber::valid($mobile);
                    $isConfiguredDemoMobile = collect(config('role_access.test_accounts', []))
                        ->contains(fn (array $account): bool => ($account['username'] ?? null) === $mobile);

                    if (! $isValidStandardMobile && ! $isConfiguredDemoMobile) {
                        $fail('شماره موبایل معتبر نیست.');
                    }
                },
            ],
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
