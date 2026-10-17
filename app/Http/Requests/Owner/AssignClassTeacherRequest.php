<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class AssignClassTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('teachers.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'انتخاب مدرس الزامی است.',
            'teacher_id.exists' => 'مدرس انتخاب‌شده معتبر نیست.',
            'classroom_id.required' => 'انتخاب کلاس الزامی است.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
        ];
    }
}
