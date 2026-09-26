<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('teachers.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'is_public' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_public.required' => 'وضعیت نمایش عمومی مشخص نشده است.',
            'is_public.boolean' => 'وضعیت نمایش عمومی معتبر نیست.',
        ];
    }
}
