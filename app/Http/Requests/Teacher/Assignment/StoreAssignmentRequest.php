<?php

namespace App\Http\Requests\Teacher\Assignment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('assignments.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:30000'],
            'handout' => ['nullable', 'file', 'mimes:pdf,txt', 'max:51200'],
            'due_at' => ['nullable', 'date', 'after_or_equal:now'],
            'max_score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('instructions')) && ! $this->hasFile('handout')) {
                $validator->errors()->add('instructions', 'دستورالعمل متنی یا فایل PDF/TXT تکلیف را اضافه کنید.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'course_id.exists' => 'دوره انتخاب‌شده معتبر نیست.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان تکلیف الزامی است.',
            'instructions.max' => 'دستورالعمل نمی‌تواند بیشتر از ۳۰٬۰۰۰ نویسه باشد.',
            'handout.mimes' => 'فایل راهنما باید PDF یا TXT باشد.',
            'handout.max' => 'حجم فایل راهنما حداکثر ۵۰ مگابایت است.',
            'due_at.date' => 'مهلت تحویل معتبر نیست.',
            'due_at.after_or_equal' => 'مهلت تحویل نمی‌تواند در گذشته باشد.',
            'max_score.min' => 'نمره کل نمی‌تواند منفی باشد.',
        ];
    }
}
