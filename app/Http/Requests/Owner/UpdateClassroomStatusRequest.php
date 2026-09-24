<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\HttpFormRequest;
use Illuminate\ValidationRule;

class UpdateClassroomStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('classrooms.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'archived'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'وضعیت کلاس الزامی است.',
            'status.in' => 'وضعیت کلاس معتبر نیست.',
        ];
    }
}