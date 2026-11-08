<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\MobileNumber;

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
            'name' => trim((string) $this->input('first_name') . ' ' . (string) $this->input('last_name')),
            'mobile' => MobileNumber::normalize((string) $this->input('mobile')),
            'national_id' => $this->normalizeDigits((string) $this->input('national_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'national_id' => ['required', 'regex:/^[0-9]{10}$/'],
            
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'mobile')],
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
            'first_name.required' => 'نام دانش‌آموز را وارد کن.',
            'last_name.required' => 'نام خانوادگی دانش‌آموز را وارد کن.',
            'name.min' => 'نام و نام خانوادگی دانش‌آموز را کامل وارد کن.',
            'national_id.required' => 'کد ملی برای جلوگیری از ساخت پروفایل تکراری الزامی است.',
            'national_id.regex' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            
            'mobile.required' => 'شماره موبایل برای ورود الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً به یک حساب متصل شده است.',
            'grade_id.required' => 'پایه تحصیلی را انتخاب کن.',
            'grade_id.exists' => 'پایه تحصیلی انتخاب‌شده معتبر نیست.',
            'birth_date.before_or_equal' => 'تاریخ تولد نمی‌تواند در آینده باشد.',
        ];
    }
}
