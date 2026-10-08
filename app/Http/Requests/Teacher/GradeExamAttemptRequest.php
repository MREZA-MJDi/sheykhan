<?php

namespace App\Http\Requests\Teacher;

use App\Models\ExamAttempt;
use Illuminate\Foundation\Http\FormRequest;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        if (!$attempt instanceof ExamAttempt || !$this->user()) {
            return false;
        }

        $attempt->loadMissing('exam:id,teacher_id');

        return (int) $attempt->exam?->teacher_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
