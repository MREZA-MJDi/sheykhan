<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('exams.attempt');
    }

    public function rules(): array
    {
        return [
            'answers' => ['sometimes', 'array', 'max:100'],
            'answers.*' => ['nullable'],
            'answer_attachments' => ['sometimes', 'array', 'max:100'],
            'answer_attachments.*' => ['nullable', 'file', 'max:20480', 'mimetypes:application/pdf,text/plain,image/jpeg,image/png,image/webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip'],
        ];
    }

    public function messages(): array
    {
        return [
            'answer_attachments.array' => 'ساختار فایل‌های پاسخ معتبر نیست.',
            'answer_attachments.max' => 'حداکثر برای ۱۰۰ سؤال می‌توانی فایل پاسخ بفرستی.',
            'answer_attachments.*.file' => 'هر پیوست پاسخ باید یک فایل معتبر باشد.',
            'answer_attachments.*.max' => 'حجم هر پیوست پاسخ حداکثر ۲۰ مگابایت است.',
            'answer_attachments.*.mimetypes' => 'برای پاسخ، PDF، متن، تصویر، Word یا ZIP انتخاب کن.',
        ];
    }
}
