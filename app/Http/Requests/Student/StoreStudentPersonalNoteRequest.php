<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

final class StoreStudentPersonalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student')
            && $this->user()?->hasPermission('notes.manage');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge(['content' => trim($this->input('content'))]);
        }
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2', 'max:20000'],
        ];
    }
}
