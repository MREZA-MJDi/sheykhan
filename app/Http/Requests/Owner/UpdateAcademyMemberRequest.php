<?php

namespace App\\Http\\Requests\\Owner;

use App\\Support\\MobileNumber;
use Illuminate\\Foundation\\Http\\FormRequest;
use Illuminate\\Validation\\Rule;

final class UpdateAcademyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = match ((string) $this->route('memberRole')) {
            'student' => 'onboarding.manage',
            'parent' => 'parents.manage',
            'teacher' => 'teachers.manage',
            default => null,
        };

        return $permission !== null
            && ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission($permission) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'mobile' => MobileNumber::normalize((string) $this->input('mobile')),
            'school_name' => filled($this->input('school_name')) ? trim((string) $this->input('school_name')) : null,
            'specialization' => filled($this->input('specialization')) ? trim((string) $this->input('specialization')) : null,
            'occupation' => filled($this->input('occupation')) ? trim((string) $this->input('occupation')) : null,
        ]);
    }

    public function rules(): array
    {
        $member = $this->route('member');
        $memberId = is_object($member) && method_exists($member, 'getKey') ? $member->getKey() : null;
        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'mobile' => [
                'required',
                'regex:/^09[0-9]{9}$/',
                Rule::unique('users', 'mobile')->ignore($memberId),
            ],
        ];

        return match ((string) $this->route('memberRole')) {
            'student' => $rules + [
                'grade_id' => ['nullable', 'integer', Rule::exists('academic_grades', 'id')->where('is_active', true)],
                'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
                'school_name' => ['nullable', 'string', 'max:255'],
            ],
            'parent' => $rules + [
                'occupation' => ['nullable', 'string', 'max:255'],
                'relation_default' => ['nullable', 'string', Rule::in(['مادر', 'پدر', 'سرپرست', 'سایر'])],
            ],
            'teacher' => $rules + [
                'specialization' => ['nullable', 'string', 'max:255'],
                'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
                'is_public' => ['required', 'boolean'],
            ],
            default => $rules,
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام و نام خانوادگی الزامی است.',
            'name.min' => 'نام و نام خانوادگی را کامل وارد کن.',
            'mobile.required' => 'شماره موبایل برای ورود الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً به حساب دیگری متصل شده است.',
            'grade_id.exists' => 'پایهٔ تحصیلی انتخاب‌شده فعال یا معتبر نیست.',
            'birth_date.before_or_equal' => 'تاریخ تولد نمی‌تواند در آینده باشد.',
            'experience_years.max' => 'سابقهٔ کاری نباید بیش از ۸۰ سال باشد.',
        ];
    }
}
