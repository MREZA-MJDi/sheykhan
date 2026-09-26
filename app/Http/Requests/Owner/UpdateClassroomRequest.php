<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('academy-owner')
            && $this->user()?->hasPermission('classrooms.manage');
    }

    public function rules(): array
    {
        $academyId = (int) $this->route('academy');
        $classroomId = (int) $this->route('classroom');

        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where(
                    fn ($query) => $query->where('academy_id', $academyId)
                ),
            ],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'grade_id' => ['nullable', 'integer', 'exists:academic_grades,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'code')
                    ->where(fn ($query) => $query->where('academy_id', $academyId))
                    ->ignore($classroomId),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'archived'])],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'course_id.exists' => 'دوره انتخاب‌شده متعلق به این آموزشگاه نیست.',
            'teacher_ids.required' => 'حداقل یک مدرس برای کلاس انتخاب کنید.',
            'teacher_ids.min' => 'کلاس باید حداقل یک مدرس داشته باشد.',
            'title.required' => 'عنوان کلاس الزامی است.',
            'code.required' => 'کد کلاس الزامی است.',
            'code.unique' => 'این کد کلاس در این آموزشگاه قبلاً استفاده شده است.',
            'capacity.min' => 'ظرفیت کلاس باید حداقل ۱ نفر باشد.',
            'ends_at.after_or_equal' => 'زمان پایان باید بعد از زمان شروع باشد.',
        ];
    }
}
