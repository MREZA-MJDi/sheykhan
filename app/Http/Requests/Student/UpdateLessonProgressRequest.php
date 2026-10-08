<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('lessons.progress');
    }

    public function rules(): array
    {
        return [
            'progress_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'seconds_watched' => ['sometimes', 'integer', 'min:0', 'max:86400'],
        ];
    }
}
