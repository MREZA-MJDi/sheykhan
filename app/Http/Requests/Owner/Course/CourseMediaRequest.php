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
        $isCover = $this->input('collection') === 'course-cover';
        $extension = strtolower((string) $this->file('media')?->getClientOriginalExtension());
        $isVideo = in_array($extension, ['mp4', 'webm'], true);

        $fileRules = match (true) {
            $isCover => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            $isVideo => ['mimes:mp4,webm', 'mimetypes:video/mp4,video/webm', 'max:524288'],
            default => ['mimes:jpg,jpeg,png,webp,pdf,zip', 'max:' . ($this->maxSizeKilobytes())],
        };

        return [
            'media' => ['required', 'file', ...$fileRules],
            'collection' => ['nullable', 'string', 'in:course-assets,course-cover'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'انتخاب فایل الزامی است.',
            'media.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'media.max' => 'حجم فایل برای این نوع محتوا نباید بیشتر از ' . $this->maxSizeMegabytes() . ' مگابایت باشد.',
            'media.mimes' => 'فرمت فایل معتبر نیست. برای ویدئو از MP4 یا WebM و برای سایر فایل‌ها از JPG، PNG، WebP، PDF یا ZIP استفاده کن.',
        ];
    }

    private function maxSizeKilobytes(): int
    {
        return $this->maxSizeMegabytes() * 1024;
    }

    private function maxSizeMegabytes(): int
    {
        return match (strtolower((string) $this->file('media')?->getClientOriginalExtension())) {
            'jpg', 'jpeg', 'png', 'webp' => 10,
            'mp4', 'webm' => 512,
            'pdf' => 50,
            'zip' => 200,
            default => 10,
        };
    }
}
