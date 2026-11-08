<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\MobileNumber;

final class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('parents.manage') ?? false);
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
            'student_id' => ['required', 'integer', 'min:1'],
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            'relation' => ['required', 'string', Rule::in(['مادر', 'پدر', 'سرپرست', 'سایر'])],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'دانش‌آموز را انتخاب کنید.',
            'first_name.required' => 'نام والد را وارد کنید.',
            'last_name.required' => 'نام خانوادگی والد را وارد کنید.',
            'mobile.required' => 'شماره موبایل والد را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'relation.required' => 'نوع ارتباط را انتخاب کنید.',
        ];
    }
}
