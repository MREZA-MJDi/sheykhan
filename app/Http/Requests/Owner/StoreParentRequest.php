<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'email' => strtolower(trim((string) $this->input('email'))),
            'mobile' => trim((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'relation' => ['required', 'string', Rule::in(['مادر', 'پدر', 'سرپرست', 'سایر'])],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'دانش‌آموز را انتخاب کنید.',
            'name.required' => 'نام والد را وارد کنید.',
            'email.required' => 'ایمیل والد را وارد کنید.',
            'relation.required' => 'نوع ارتباط را انتخاب کنید.',
        ];
    }
}
