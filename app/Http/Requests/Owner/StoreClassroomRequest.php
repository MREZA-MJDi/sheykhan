<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('classrooms.manage') ?? false;
    }

    public function rules(): array
    {
        $academy = $this->route('academy');

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'code')->where(fn ($query) => $query->where('academy_id', $academy?->id)),
            ],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', Rule::in(['active', 'archived'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'title.required' => 'عنوان کلاس الزامی است.',
            'code.required' => 'کد کلاس الزامی است.',
            'code.unique' => 'این کد کلاس قبلاً در آموزشگاه استفاده شده است.',
            'capacity.min' => 'ظرفیت کلاس باید حداقل ۱ نفر باشد.',
            'ends_at.after_or_equal' => 'زمان پایان باید بعد از شروع باشد.',
        ];
    }
}
