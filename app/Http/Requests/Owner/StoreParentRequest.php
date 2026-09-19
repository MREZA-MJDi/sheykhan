<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('parents.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'occupation' => ['nullable','string','max:255'],
            'relation_default' => ['nullable','string','max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام والد الزامی است.',
            'email.required' => 'ایمیل والد الزامی است.',
            'email.unique' => 'این ایمیل قبلاً استفاده شده است.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور صحیح نیست.',
        ];
    }
}
