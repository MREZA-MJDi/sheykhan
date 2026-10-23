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
            'questions.*.options' => ['nullable', 'array', 'max:30'],
            'questions.*.options.*' => ['nullable', 'string', 'max:500'],
            'questions.*.correct_answer' => ['nullable'],
            'questions.*.expected_answer' => ['nullable', 'string', 'max:15000'],
            'questions.*.correct_answer.*' => ['string', 'max:500'],
            'questions.*.score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $questions = $this->input('questions', []);
            if (! is_array($questions)) {
                return;
            }

            $status = $this->input('status', 'draft');
            $hasQuestion = count(array_filter($questions, 'is_array')) > 0;

            if ($status === 'published' && ! $hasQuestion) {
                $validator->errors()->add('questions', 'برای انتشار آزمون حداقل یک سؤال لازم است.');
            }

            foreach ($questions as $index => $question) {
                if (! is_array($question)) {
                    $validator->errors()->add("questions.{$index}", 'ساختار سؤال معتبر نیست.');
                    continue;
                }

                $type = $question['type'] ?? 'text';
                if (! in_array($type, ['text', 'single', 'multiple', 'checkbox'], true)) {
                    continue;
                }

                if ($type === 'text') {
                    continue;
                }

                $options = array_values(array_filter(
                    (array) ($question['options'] ?? []),
                    static fn ($option) => is_string($option) && trim($option) !== ''
                ));

                if ($type === 'single' && count($options) !== 4) {
                    $validator->errors()->add("questions.{$index}.options", 'برای سؤال چهارگزینه‌ای باید دقیقاً چهار گزینه وارد کنی.');
                }

                if (count($options) < 2) {
                    $validator->errors()->add("questions.{$index}.options", 'برای سؤال گزینه‌ای حداقل دو گزینه لازم است.');
                    continue;
                }

                if (count(array_unique($options)) !== count($options)) {
                    $validator->errors()->add("questions.{$index}.options", 'گزینه‌های تکراری مجاز نیستند؛ هر گزینه را فقط یک بار وارد کن.');
                }

                $correct = $question['correct_answer'] ?? null;
                $multi = in_array($type, ['multiple', 'checkbox'], true);

                if ($multi) {
                    if (! is_array($correct) || $correct === []) {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'برای سؤال چندپاسخی دست‌کم یک پاسخ صحیح را انتخاب کن.');
                        continue;
                    }

                    $correct = array_values(array_unique(array_map(
                        static fn ($value) => is_string($value) ? trim($value) : '',
                        $correct
                    )));

                    if (array_filter($correct, static fn ($value) => $value === '') !== []) {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'پاسخ صحیح چندپاسخی معتبر نیست.');
                        continue;
                    }

                    if (array_diff($correct, $options) !== []) {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'تمام پاسخ‌های صحیح باید از بین گزینه‌های همین سؤال انتخاب شوند.');
                    }
                } else {
                    if (! is_string($correct) || trim($correct) === '') {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'یک پاسخ صحیح برای این سؤال انتخاب کن.');
                        continue;
                    }

                    if (! in_array(trim($correct), $options, true)) {
                        $validator->errors()->add("questions.{$index}.correct_answer", 'پاسخ صحیح باید دقیقاً یکی از گزینه‌های همین سؤال باشد.');
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
            'questions.*.question.required_with' => 'متن همه سؤال‌ها را وارد کن.',
        ];
    }
}
