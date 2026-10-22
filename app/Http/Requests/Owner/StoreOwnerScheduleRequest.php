<?php

namespace App\Http\Requests\Owner;

use App\Services\MeetingLinkService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOwnerScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('live_classes.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:255'],
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
        ];
    }

    public function messages(): array
    {
        return [
            'weekday.required' => 'روز هفته را انتخاب کنید.',
            'weekday.between' => 'روز هفته معتبر نیست.',
            'start_time.required' => 'ساعت شروع الزامی است.',
            'start_time.date_format' => 'ساعت شروع معتبر نیست.',
            'end_time.required' => 'ساعت پایان الزامی است.',
            'end_time.after' => 'ساعت پایان باید بعد از شروع باشد.',
            'provider.required' => 'سرویس جلسه را انتخاب کنید.',
            'provider.in' => 'سرویس جلسه انتخاب‌شده معتبر نیست.',
            'meeting_url.required' => 'برای این سرویس باید لینک واقعی جلسه را وارد کنید.',
            'meeting_url.url' => 'لینک ورود به جلسه معتبر نیست.',
        ];
    }
}
