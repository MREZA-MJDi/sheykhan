<?php

namespace App\Http\Requests\Teacher;

use App\Models\ExamAttempt;
use Illuminate\Foundation\Http\FormRequest;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt instanceof ExamAttempt
            && $attempt->exam?->teacher_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
