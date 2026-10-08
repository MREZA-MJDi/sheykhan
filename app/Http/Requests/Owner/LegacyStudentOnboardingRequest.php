<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class LegacyStudentOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('onboarding.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'national_id' => ['required', 'regex:/^[0-9۰-۹٠-٩]{10}$/'],
            'grade_id' => ['required', 'integer', 'exists:academic_grades,id'],
            'mobile' => ['nullable', 'regex:/^(?:\+98|0098|98|0)?9[0-9۰-۹٠-٩]{9}$/'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی الزامی است.',
            'national_id.required' => 'کد ملی الزامی است.',
            'national_id.regex' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'grade_id.required' => 'انتخاب پایه الزامی است.',
            'grade_id.exists' => 'پایه انتخاب‌شده معتبر نیست.',
            'mobile.regex' => 'شماره موبایل واردشده معتبر نیست.',
            'idempotency_key.required' => 'شناسه عملیات الزامی است.',
            'idempotency_key.uuid' => 'شناسه عملیات معتبر نیست.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'national_id' => $this->normalizeDigits((string) $this->input('national_id')),
            'mobile' => $this->normalizeDigits((string) $this->input('mobile')),
        ]);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
};