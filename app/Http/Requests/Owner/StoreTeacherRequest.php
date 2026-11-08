<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\MobileNumber;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('teachers.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['mobile' => MobileNumber::normalize((string) $this->input('mobile'))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/', 'unique:users,mobile'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام مدرس الزامی است.',
            'mobile.required' => 'شماره موبایل مدرس الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        ];
    }
}
