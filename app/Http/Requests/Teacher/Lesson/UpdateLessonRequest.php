<?php

namespace App\Http\Requests\Teacher\Lesson;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('lessons.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_section_id' => ['sometimes', 'required', 'integer', 'exists:course_sections,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash'],
            'type' => ['sometimes', Rule::in(['video', 'text', 'quiz', 'live', 'file'])],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'duration_seconds' => ['sometimes', 'integer', 'min:0'],
            'is_free' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_section_id.required' => 'انتخاب سرفصل الزامی است.',
            'course_section_id.exists' => 'سرفصل انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان درس الزامی است.',
            'slug.required' => 'شناسه درس الزامی است.',
            'slug.alpha_dash' => 'شناسه درس فقط باید شامل حروف، عدد، خط تیره و زیرخط باشد.',
            'summary.max' => 'خلاصه درس نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'duration_seconds.min' => 'مدت درس نمی‌تواند منفی باشد.',
        ];
    }
}
