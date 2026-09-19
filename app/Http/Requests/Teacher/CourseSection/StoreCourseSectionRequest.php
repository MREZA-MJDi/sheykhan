<?php

namespace App\Http\Requests\Teacher\CourseSection;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseSectionRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان سرفصل الزامی است.',
            'title.max' => 'عنوان سرفصل طولانی است.',
        ];
    }
}