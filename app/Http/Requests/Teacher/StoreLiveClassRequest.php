<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLiveClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('live_classes.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'provider' => ['nullable', 'string', 'max:100'],
            'meeting_url' => ['nullable', 'url', 'max:2000'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'status' => ['sometimes', Rule::in(['scheduled', 'live', 'ended', 'cancelled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'title.required' => 'عنوان جلسه زنده الزامی است.',
            'meeting_url.url' => 'لینک جلسه معتبر نیست.',
            'scheduled_at.required' => 'زمان برگزاری الزامی است.',
            'duration_minutes.min' => 'مدت جلسه باید حداقل یک دقیقه باشد.',
        ];
    }
}
