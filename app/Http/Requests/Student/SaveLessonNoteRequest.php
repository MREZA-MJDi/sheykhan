<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SaveLessonNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('notes.manage');
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:20000'],
        ];
    }
}
