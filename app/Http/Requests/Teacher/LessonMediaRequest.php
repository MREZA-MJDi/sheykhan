<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('media.upload') ?? false;
    }

    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                'max:512000',
                'mimetypes:video/mp4,video/webm,video/quicktime,application/pdf,image/jpeg,image/png,image/webp,application/zip',
            ],
            'collection' => [
                'required',
                Rule::in(['video', 'pdf', 'resource', 'thumbnail']),
            ],
            'access' => [
                'required',
                Rule::in(['course', 'free', 'paid']),
            ],
            'downloadable' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'انتخاب فایل الزامی است.',
            'media.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'media.max' => 'حجم فایل نمی‌تواند بیشتر از ۵۰۰ مگابایت باشد.',
            'media.mimetypes' => 'نوع فایل انتخاب‌شده مجاز نیست.',
            'collection.required' => 'نوع فایل را مشخص کن.',
            'collection.in' => 'نوع فایل معتبر نیست.',
            'access.required' => 'سطح دسترسی فایل را مشخص کن.',
            'access.in' => 'سطح دسترسی فایل معتبر نیست.',
        ];
    }
}
