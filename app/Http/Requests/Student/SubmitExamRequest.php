<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('exams.attempt');
    }

    public function rules(): array
    {
        return [
            'answers' => ['sometimes', 'array', 'max:100'],
            'answers.*' => ['nullable'],
        ];
    }
}
