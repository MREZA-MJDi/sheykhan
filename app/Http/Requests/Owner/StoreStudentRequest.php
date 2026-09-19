<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('students.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'student_number' => ['nullable','string','max:100','unique:student_profiles,student_number'],
            'birth_date' => ['nullable','date','before:today'],
            'grade' => ['nullable','string','max:100'],
            'school_name' => ['nullable','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام دانش‌آموز الزامی است.',
            'email.required' => 'ایمیل دانش‌آموز الزامی است.',
            'email.unique' => 'این ایمیل قبلاً استفاده شده است.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور صحیح نیست.',
            'student_number.unique' => 'این شماره دانش‌آموزی قبلاً استفاده شده است.',
            'birth_date.before' => 'تاریخ تولد معتبر نیست.',
        ];
    }
}
