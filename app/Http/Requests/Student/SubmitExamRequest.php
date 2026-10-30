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
            'attachments' => ['sometimes', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,txt,jpg,jpeg,png,webp,zip,doc,docx,odt'],
        ];
    }
}
