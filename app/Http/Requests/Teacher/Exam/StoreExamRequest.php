<?php

namespace App\Http\Requests\Teacher\Exam;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('exams.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'attempts_allowed' => ['sometimes', 'integer', 'min:1', 'max:255'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'closed'])],
            'questions' => ['nullable', 'array', 'max:100'],
            'questions.*.type' => ['required_with:questions', Rule::in(['text', 'single', 'multiple', 'checkbox'])],
            'questions.*.question' => ['required_with:questions', 'string', 'max:5000'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.correct_answer' => ['nullable'],
            'questions.*.score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $questions = $this->input('questions', []);
            $status = $this->input('status', 'draft');

            if ($status === 'published' && count($questions) === 0) {
                $validator->errors()->add('questions', 'برای انتشار آزمون حداقل یک سؤال لازم است.');
            }

            foreach ($questions as $index => $question) {
                $type = $question['type'] ?? 'text';

                if (in_array($type, ['single', 'multiple', 'checkbox'], true)) {
                    $options = array_values(array_filter(
                        (array) ($question['options'] ?? []),
                        static fn ($option) => trim((string) $option) !== ''
                    ));

                    if (count($options) < 2) {
                        $validator->errors()->add("questions.{$index}.options", 'برای سؤال تستی حداقل دو گزینه لازم است.');
                    }

                    if (($question['correct_answer'] ?? null) === null || ($question['correct_answer'] ?? '') === '') {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'پاسخ صحیح سؤال تستی را مشخص کن.');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'course_id.exists' => 'دوره انتخاب‌شده معتبر نیست.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان آزمون الزامی است.',
            'duration_minutes.min' => 'مدت آزمون نمی‌تواند منفی باشد.',
            'ends_at.after_or_equal' => 'زمان پایان آزمون باید بعد از زمان شروع باشد.',
            'attempts_allowed.min' => 'تعداد دفعات مجاز باید حداقل ۱ باشد.',
        ];
    }
}
