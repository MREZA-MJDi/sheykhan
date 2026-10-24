<?php

namespace App\Http\Requests\Teacher;

use App\Models\Course;
use App\Services\CourseManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            && (app(CourseManagementService::class)->teacherCanManage($this->user(), $course));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => blank($this->input('slug')) ? null : trim((string) $this->input('slug')),
            'price' => $this->input('access_type') === 'free' ? 0 : $this->input('price'),
        ]);
    }

    public function rules(): array
    {
        /** @var Course|null $course */
        $course = $this->route('course');
        $academyId = $course?->academy_id;

        return [
            'academy_id' => [
                'sometimes', 'required', 'integer',
                Rule::in([$academyId]),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('courses', 'slug')
                    ->where(fn ($query) => $query->where('academy_id', $academyId))
                    ->ignore($course?->id),
            ],
            'level' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'access_type' => ['sometimes', Rule::in(['free', 'paid'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'price' => [
                Rule::requiredIf(fn () => $this->input('access_type') === 'paid'),
                'nullable', 'numeric', 'min:0', 'max:999999999999.99',
                Rule::when(
                    ($this->input('access_type') ?? $course?->access_type) === 'paid',
                    ['min:0.01']
                ),
            ],
            'published_at' => ['nullable', 'date'],
            'spotplayer_course_id' => ['sometimes', 'nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }
}
