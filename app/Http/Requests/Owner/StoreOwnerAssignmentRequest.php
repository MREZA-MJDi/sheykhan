<?php

namespace App\Http\Requests\Owner;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOwnerAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('courses.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:30000'],
            'due_at' => ['nullable', 'date', 'after_or_equal:now'],
            'max_score' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'handout' => ['nullable', 'file', 'mimes:pdf,txt', 'max:51200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (blank($this->input('instructions')) && ! $this->hasFile('handout')) {
                $validator->errors()->add('instructions', 'دستورالعمل متنی یا فایل PDF تکلیف را اضافه کنید.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'کلاس هدف را انتخاب کنید.',
            'classroom_id.exists' => 'کلاس انتخاب‌شده معتبر نیست.',
            'title.required' => 'عنوان تکلیف الزامی است.',
            'title.max' => 'عنوان تکلیف نمی‌تواند بیشتر از ۲۵۵ نویسه باشد.',
            'instructions.max' => 'دستورالعمل نمی‌تواند بیشتر از ۳۰٬۰۰۰ نویسه باشد.',
            'due_at.date' => 'مهلت تحویل معتبر نیست.',
            'due_at.after_or_equal' => 'مهلت تحویل نمی‌تواند در گذشته باشد.',
            'max_score.numeric' => 'نمره کل باید عددی باشد.',
            'max_score.min' => 'نمره کل نمی‌تواند منفی باشد.',
            'status.in' => 'وضعیت تکلیف معتبر نیست.',
            'handout.mimes' => 'فایل پیوست باید PDF یا TXT باشد.',
            'handout.max' => 'حجم فایل راهنما حداکثر ۵۰ مگابایت است.',
        ];
    }
}
