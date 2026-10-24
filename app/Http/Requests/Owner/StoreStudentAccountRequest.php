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
            'national_id' => $this->normalizeDigits((string) $this->input('national_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'national_id' => ['required', 'regex:/^[0-9]{10}$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['nullable', 'string', 'max:30', 'regex:/^(?:\+98|0098|98|0)?9[0-9۰-۹٠-٩]{9}$/', Rule::unique('users', 'mobile')],
            'grade_id' => ['required', 'integer', 'exists:academic_grades,id'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function normalizeDigits(string $value): string
    {
        return strtr(trim($value), [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی دانش‌آموز را وارد کن.',
            'name.min' => 'نام دانش‌آموز باید حداقل دو نویسه باشد.',
            'national_id.required' => 'کد ملی برای جلوگیری از ساخت پروفایل تکراری الزامی است.',
            'national_id.regex' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
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
