<?php

namespace App\Http\Requests\Teacher;

use App\Services\MeetingLinkService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('live_classes.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:255'],
            'provider' => ['required', Rule::in([MeetingLinkService::SKYROOM])],
            'meeting_url' => [
                'required',
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
            'provider.required' => 'سرویس جلسه را انتخاب کن.',
            'provider.in' => 'سرویس جلسه انتخاب‌شده معتبر نیست.',
            'meeting_url.required' => 'لینک اتاق واقعی Skyroom را وارد کن.',
            'meeting_url.url' => 'لینک جلسه معتبر نیست.',
        ];
    }
}
