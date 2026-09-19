<?php

namespace App\Http\Requests\Teacher\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('lessons.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}