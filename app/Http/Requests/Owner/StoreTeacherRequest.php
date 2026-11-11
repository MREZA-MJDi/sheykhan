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
        $this->merge([
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'name' => trim((string) $this->input('first_name') . ' ' . (string) $this->input('last_name')),
            'mobile' => MobileNumber::normalize((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/', 'unique:users,mobile'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'is_public' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'نام مدرس الزامی است.',
            'last_name.required' => 'نام خانوادگی مدرس الزامی است.',
            'mobile.required' => 'شماره موبایل مدرس الزامی است.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        ];
    }
}
