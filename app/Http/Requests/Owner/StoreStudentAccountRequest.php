<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('onboarding.manage') ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'mobile' => trim((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['nullable', 'string', 'max:30', 'regex:/^(?:\+98|0098|98|0)?9[0-9۰-۹٠-٩]{9}$/', Rule::unique('users', 'mobile')],
            'grade_id' => ['required', 'integer', 'exists:academic_grades,id'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'school_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی دانش‌آموز را وارد کن.',
            'name.min' => 'نام دانش‌آموز باید حداقل دو نویسه باشد.',
            'email.required' => 'ایمیل دانش‌آموز برای ورود به حساب لازم است.',
            'email.email' => 'ایمیل واردشده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلاً به یک حساب متصل شده است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً به یک حساب متصل شده است.',
            'grade_id.required' => 'پایه تحصیلی را انتخاب کن.',
            'grade_id.exists' => 'پایه تحصیلی انتخاب‌شده معتبر نیست.',
            'birth_date.before_or_equal' => 'تاریخ تولد نمی‌تواند در آینده باشد.',
        ];
    }
}
