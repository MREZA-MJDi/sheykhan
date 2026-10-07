<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('assignments.submit');
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:30000'],
            'attachments' => ['sometimes', 'array', 'max:3'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimetypes:application/pdf,text/plain,image/jpeg,image/png,image/webp,application/zip',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (!$this->filled('content') && !$this->hasFile('attachments')) {
                $validator->errors()->add('content', 'متن پاسخ یا حداقل یک فایل برای ارسال الزامی است.');
            }
        });
    }
}
