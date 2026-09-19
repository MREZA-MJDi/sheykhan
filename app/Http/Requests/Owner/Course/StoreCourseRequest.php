<?php

namespace App\Http\Requests\Owner\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('courses.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('access_type') === 'free') {
            $this->merge(['price' => 0]);
        }
    }

    public function rules(): array
    {
        $academyId = $this->integer('academy_id');

        return [
            'academy_id' => ['required', 'integer', 'exists:academies,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('courses', 'slug')->where(fn ($query) => $query->where('academy_id', $academyId)),
            ],
            'level' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'access_type' => ['required', Rule::in(['free', 'paid'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'price' => ['required_if:access_type,paid', 'nullable', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'academy_id.required' => 'انتخاب آموزشگاه الزامی است.',
            'academy_id.exists' => 'آموزشگاه انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان دوره الزامی است.',
            'slug.required' => 'شناسه دوره الزامی است.',
            'slug.alpha_dash' => 'شناسه دوره فقط باید شامل حروف، عدد، خط تیره و زیرخط باشد.',
            'slug.unique' => 'این شناسه دوره در این آموزشگاه قبلاً استفاده شده است.',
            'access_type.required' => 'مشخص‌کردن رایگان یا پولی بودن دوره الزامی است.',
            'access_type.in' => 'نوع دسترسی دوره معتبر نیست.',
            'price.required_if' => 'برای دوره پولی، قیمت را وارد کنید.',
            'price.min' => 'قیمت دوره پولی باید بیشتر از صفر باشد.',
            'short_description.max' => 'خلاصه دوره نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'duration_minutes.min' => 'مدت زمان نمی‌تواند منفی باشد.',
            'published_at.date' => 'تاریخ انتشار معتبر نیست.',
        ];
    }
}
