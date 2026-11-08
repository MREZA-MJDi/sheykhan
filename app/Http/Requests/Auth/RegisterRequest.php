<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
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
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'mobile' => \\App\\Support\\MobileNumber::normalize((string) $this->input('mobile')),
            'account_type' => $this->input('account_type', 'student'),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/', 'unique:users,mobile'],
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
            'first_name.required' => 'نام را وارد کنید.',
            'last_name.required' => 'نام خانوادگی را وارد کنید.',
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'name.min' => 'نام باید حداقل ۲ کاراکتر باشد.',

            'cf-turnstile-response.required' => 'لطفاً تأیید ضدربات را انجام بده.',
            'account_type.in' => 'نوع حساب انتخاب‌شده معتبر نیست.',
        ];
    }
}
