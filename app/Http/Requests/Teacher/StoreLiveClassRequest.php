<?php

namespace App\Http\Requests\Teacher;

use App\Services\MeetingLinkService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLiveClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('live_classes.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'provider' => ['required', Rule::in([
                MeetingLinkService::JITSI,
                MeetingLinkService::GOOGLE_MEET,
                MeetingLinkService::ZOOM,
                MeetingLinkService::OTHER,
            ])],
            'meeting_url' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $this->input('provider') !== MeetingLinkService::JITSI),
                'url',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (blank($value)) {
                        return;
                    }

                    $service = app(MeetingLinkService::class);
                    $provider = (string) $this->input('provider');

                    if (! $service->urlMatchesProvider($provider, (string) $value)) {
                        $fail($service->validationMessage($provider));
                    }
                },
            ],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'status' => ['sometimes', Rule::in(['scheduled', 'live', 'ended', 'cancelled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'انتخاب دوره الزامی است.',
            'title.required' => 'عنوان جلسه زنده الزامی است.',
            'provider.required' => 'سرویس جلسه را انتخاب کن.',
            'provider.in' => 'سرویس جلسه انتخاب‌شده معتبر نیست.',
            'meeting_url.required' => 'برای این سرویس باید لینک جلسهٔ ساخته‌شده را وارد کنی.',
            'meeting_url.url' => 'لینک جلسه معتبر نیست.',
            'scheduled_at.required' => 'زمان برگزاری الزامی است.',
            'duration_minutes.min' => 'مدت جلسه باید حداقل یک دقیقه باشد.',
        ];
    }
}
