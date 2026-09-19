<?php

namespace App\Http\Requests\Teacher\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('exams.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'attempts_allowed' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'course_id.exists' => 'دوره انتخاب‌شده معتبر نیست.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان آزمون الزامی است.',
            'duration_minutes.min' => 'مدت آزمون نمی‌تواند منفی باشد.',
            'ends_at.after_or_equal' => 'زمان پایان آزمون باید بعد از زمان شروع باشد.',
            'attempts_allowed.min' => 'تعداد دفعات مجاز باید حداقل ۱ باشد.',
        ];
    }
}
