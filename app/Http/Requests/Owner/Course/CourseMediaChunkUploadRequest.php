<?php

namespace App\Http\Requests\Owner\Course;

use App\Models\Course;
use App\Services\CourseManagementService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseMediaChunkUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');
        $user = $this->user();

        if (! $course || ! $user || ! app(CourseManagementService::class)->canManage($user, $course)) {
            return false;
        }

        return $this->input('collection') !== 'course-cover'
            || ($this->routeIs('owner.*') && $user->hasRole('academy-owner'));
    }

    public function rules(): array
    {
        return [
            'upload_id' => ['required', 'uuid'],
            'chunk_index' => ['required', 'integer', 'min:0', 'max:110'],
            'total_chunks' => ['required', 'integer', 'min:1', 'max:110'],
            'file_name' => ['required', 'string', 'max:255'],
            'file_size' => ['required', 'integer', 'min:1', 'max:536870912'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'collection' => ['required', Rule::in(['course-assets', 'course-cover'])],
            'chunk' => ['required', 'file', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'upload_id.uuid' => 'شناسه ادامه آپلود معتبر نیست؛ صفحه را تازه‌سازی و دوباره انتخاب کن.',
            'chunk_index.required' => 'شماره بخش ارسالی مشخص نیست.',
            'total_chunks.required' => 'تعداد بخش‌های فایل مشخص نیست.',
            'file_name.required' => 'نام فایل مشخص نیست.',
            'file_size.max' => 'حجم فایل از سقف ۵۱۲ مگابایت بیشتر است.',
            'collection.in' => 'نوع فایل مقصد معتبر نیست.',
            'chunk.max' => 'هر بخش آپلود نباید بیشتر از ۵ مگابایت باشد.',
        ];
    }
}
