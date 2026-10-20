<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

final class StoreAcademyMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('live_classes.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'meeting_target' => ['required', 'string', 'regex:/^\d+:\d+:\d+$/'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1500'],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'meeting_target.required' => 'کلاس، دانش‌آموز و ولی را انتخاب کنید.',
            'meeting_target.regex' => 'مخاطب جلسه معتبر نیست؛ صفحه را تازه‌سازی کنید.',
            'title.required' => 'عنوان جلسه الزامی است.',
            'title.max' => 'عنوان جلسه نمی‌تواند بیشتر از ۲۵۵ نویسه باشد.',
            'message.max' => 'پیام اطلاع‌رسانی نمی‌تواند بیشتر از ۱۵۰۰ نویسه باشد.',
            'scheduled_at.required' => 'تاریخ و ساعت جلسه را وارد کنید.',
            'scheduled_at.date' => 'تاریخ و ساعت جلسه معتبر نیست.',
            'scheduled_at.after_or_equal' => 'زمان جلسه نمی‌تواند در گذشته باشد.',
            'scheduled_end_at.after' => 'پایان جلسه باید بعد از زمان شروع باشد.',
        ];
    }
}
