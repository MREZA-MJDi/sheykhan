<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('classrooms.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'title.required' => 'عنوان کلاس الزامی است.',
            'code.required' => 'کد کلاس الزامی است.',
            'capacity.min' => 'ظرفیت کلاس باید حداقل ۱ نفر باشد.',
            'ends_at.after_or_equal' => 'زمان پایان باید بعد از زمان شروع باشد.',
        ];
    }
}
