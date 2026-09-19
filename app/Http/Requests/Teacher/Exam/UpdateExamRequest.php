<?php

namespace App\Http\Requests\Teacher\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'attempts_allowed' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'questions' => ['nullable', 'array', 'max:100'],
            'questions.*.type' => ['required_with:questions', Rule::in(['text', 'single', 'multiple', 'checkbox'])],
            'questions.*.question' => ['required_with:questions', 'string', 'max:5000'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.correct_answer' => ['nullable'],
            'questions.*.score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}
