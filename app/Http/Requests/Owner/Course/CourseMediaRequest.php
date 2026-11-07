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

        return [
            'media' => [
                'required',
                'file',
                ...($isCover ? ['image'] : []),
                'max:' . ($isCover ? 10240 : $this->maxSizeKilobytes()),
                $isCover ? 'mimes:jpg,jpeg,png,webp' : 'mimes:jpg,jpeg,png,webp,pdf,zip',
            ],
            'collection' => ['nullable', 'string', 'in:course-assets,course-cover'],
        ];
    }
    public function messages(): array
    {
        return [
            'media.required' => 'انتخاب فایل الزامی است.',
            'media.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'media.max' => 'حجم فایل برای این نوع محتوا نباید بیشتر از ' . $this->maxSizeMegabytes() . ' مگابایت باشد.',
            'media.mimes' => 'برای فایل‌های این بخش از JPG، PNG، WebP، PDF یا ZIP استفاده کن؛ ویدئو باید در SpotPlayer بارگذاری شود.',
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
            'pdf' => 50,
            'zip' => 200,
            'mp4', 'webm', 'mov' => 500,
            default => 10,
        };
    }
}
