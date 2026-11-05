<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Http;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'account_type' => $this->input('account_type', 'student'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'account_type' => ['required', Rule::in(['student', 'parent'])],
            'cf-turnstile-response' => [Rule::requiredIf((bool) config('services.turnstile.enabled')), 'nullable', 'string', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! config('services.turnstile.enabled') || blank($this->input('cf-turnstile-response'))) {
                return;
            }

            $secret = (string) config('services.turnstile.secret_key');
            if ($secret === '') {
                $validator->errors()->add('cf-turnstile-response', 'سرویس ضدربات هنوز در سرور پیکربندی نشده است.');
                return;
            }

            try {
                $response = Http::asForm()
                    ->timeout(5)
                    ->post((string) config('services.turnstile.verify_url'), [
                        'secret' => $secret,
                        'response' => (string) $this->input('cf-turnstile-response'),
                        'remoteip' => $this->ip(),
                    ]);

                $expectedHostname = (string) $this->getHost();
                $verified = $response->successful()
                    && $response->json('success') === true
                    && hash_equals($expectedHostname, (string) $response->json('hostname'))
                    && $response->json('action') === 'register';

                if (! $verified) {
                    $validator->errors()->add('cf-turnstile-response', 'تأیید ضدربات انجام نشد؛ دوباره تلاش کن.');
                }
            } catch (\Throwable) {
                $validator->errors()->add('cf-turnstile-response', 'ارتباط با سرویس ضدربات برقرار نشد؛ کمی بعد دوباره تلاش کن.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی را وارد کنید.',
            'name.min' => 'نام باید حداقل ۲ کاراکتر باشد.',
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'cf-turnstile-response.required' => 'لطفاً تأیید ضدربات را انجام بده.',
            'account_type.in' => 'نوع حساب انتخاب‌شده معتبر نیست.',
        ];
    }
}
