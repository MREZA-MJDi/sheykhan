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
        $collection = (string) $this->input('collection');
        $isCover = $collection === 'course-cover';
        $isTrailer = $collection === 'course-trailer';

        return [
            'media' => [
                'required',
                'file',
                ...($isCover ? ['image'] : []),
                'max:' . ($isCover ? 10240 : ($isTrailer ? 204800 : $this->maxSizeKilobytes())),
                $isCover
                    ? 'mimes:jpg,jpeg,png,webp'
                    : ($isTrailer ? 'mimes:mp4,webm,mov' : 'mimes:jpg,jpeg,png,webp,pdf,zip'),
            ],
            'collection' => ['required', 'string', 'in:course-assets,course-cover,course-trailer'],
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'انتخاب فایل الزامی است.',
            'media.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'media.max' => 'حجم فایل برای این بخش نباید بیشتر از ' . $this->maxSizeMegabytes() . ' مگابایت باشد.',
            'media.mimes' => 'برای کاور JPG، PNG یا WebP و برای تریلر MP4، WebM یا MOV انتخاب کن. ویدئوهای کامل درس باید در SpotPlayer بارگذاری شوند.',
            'collection.in' => 'نوع رسانهٔ انتخاب‌شده معتبر نیست.',
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
            'mp4', 'webm', 'mov' => 200,
            default => 10,
        };
    }
}
