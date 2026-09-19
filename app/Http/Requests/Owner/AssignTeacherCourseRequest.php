<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeacherCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('teachers.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ];
    }
}
