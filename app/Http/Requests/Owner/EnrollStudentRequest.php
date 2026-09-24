<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class EnrollStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasPermission('enrollments.manage') ?? false)
            && ($this->user()?->hasRole('academy-owner') ?? false);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'انتخاب دانش‌آموز الزامی است.',
            'student_id.exists' => 'دانش‌آموز انتخاب‌شده معتبر نیست.',
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'course_id.exists' => 'دوره انتخاب‌شده معتبر نیست.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
            'paid_amount.numeric' => 'مبلغ پرداختی باید عددی باشد.',
            'paid_amount.min' => 'مبلغ پرداختی نمی‌تواند منفی باشد.',
            'idempotency_key.required' => 'شناسه عملیات الزامی است.',
            'idempotency_key.uuid' => 'شناسه عملیات معتبر نیست.',
        ];
    }
}
