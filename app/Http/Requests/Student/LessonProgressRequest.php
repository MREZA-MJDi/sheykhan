<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class LessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'seconds_watched' => ['required', 'numeric', 'min:0'],
            'completed' => ['sometimes', 'boolean'],
        ];
    }
}
