<?php

namespace App\Http\Requests\Owner\Academy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('academy.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:academies,slug'],
            'code' => ['nullable', 'string', 'max:255', 'unique:academies,code'],
            'description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام آموزشگاه الزامی است.',
            'slug.required' => 'شناسه آموزشگاه الزامی است.',
            'slug.alpha_dash' => 'شناسه آموزشگاه فقط باید شامل حروف، عدد، خط تیره و زیرخط باشد.',
            'slug.unique' => 'این شناسه آموزشگاه قبلاً ثبت شده است.',
            'code.unique' => 'کد آموزشگاه قبلاً ثبت شده است.',
            'email.email' => 'ایمیل واردشده معتبر نیست.',
            'website.url' => 'آدرس وب‌سایت معتبر نیست.',
        ];
    }
}
