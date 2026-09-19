<?php

namespace App\Http\Requests\Teacher;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeAssignmentSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof Assignment
            && $assignment->teacher_id === $this->user()?->id;
    }

    public function rules(): array
    {
        $assignment = $this->route('assignment');

        return [
            'score' => [
                'required',
                'numeric',
                'min:0',
                Rule::when(
                    $assignment instanceof Assignment && $assignment->max_score !== null,
                    'max:' . (float) $assignment->max_score
                ),
            ],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'نمره را وارد کنید.',
            'score.numeric' => 'نمره باید عددی باشد.',
            'score.min' => 'نمره نمی‌تواند منفی باشد.',
            'score.max' => 'نمره از سقف تعیین‌شده بیشتر نمی‌تواند باشد.',
            'feedback.max' => 'بازخورد نمی‌تواند بیشتر از ۵۰۰۰ کاراکتر باشد.',
        ];
    }
}
