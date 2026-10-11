<?php

namespace App\Http\Requests\Teacher;

use App\Models\Academy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('teacher') ?? false)
            && ($this->user()?->hasPermission('courses.manage') ?? false);
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
        $academyId = $this->integer('academy_id');

        return [
            'academy_id' => ['required', 'integer', Rule::in($this->teacherAcademyIds())],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('courses', 'slug')->where(fn ($query) => $query->where('academy_id', $academyId)),
            ],
            'level' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'access_type' => ['required', Rule::in(['free', 'paid'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'price' => [
                Rule::requiredIf(fn () => $this->input('access_type') === 'paid'),
                'nullable', 'numeric', 'min:0', 'max:999999999999.99',
                Rule::when($this->input('access_type') === 'paid', ['min:0.01']),
            ],
            'published_at' => ['nullable', 'date'],
        ];
    }

    private function teacherAcademyIds(): array
    {
        $teacher = $this->user();
        if (! $teacher) {
            return [];
        }

        return Academy::query()
            ->where('status', 'active')
            ->whereHas('users', fn ($query) => $query
                ->whereKey($teacher->id)
                ->where('academy_user.role', 'teacher')
                ->where('academy_user.status', 'active'))
            ->pluck('id')
            ->all();
    }
}
