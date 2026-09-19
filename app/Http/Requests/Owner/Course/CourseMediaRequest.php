<?php

namespace App\Http\Requests\Owner\Course;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class CourseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return $course
            && $this->user()?->hasPermission('media.upload')
            && app(\App\Services\CourseManagementService::class)->canManage($this->user(), $course);
    }

    public function rules(): array
    {
        return [
            'media' => [
                'required',
                'file',
                'max:512000',
                'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov,zip',
            ],
            'collection' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'انتخاب فایل الزامی است.',
            'media.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'media.max' => 'حجم فایل نباید بیشتر از ۵۰۰ مگابایت باشد.',
            'media.mimes' => 'فرمت فایل پشتیبانی نمی‌شود.',
        ];
    }
}
