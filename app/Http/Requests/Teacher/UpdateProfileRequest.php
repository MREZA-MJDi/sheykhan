<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\MobileNumber;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('teacher')
            && $this->user()?->hasPermission('profile.manage');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'name' => trim((string) $this->input('first_name') . ' ' . (string) $this->input('last_name')),
            'mobile' => MobileNumber::normalize((string) $this->input('mobile')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'name' => ['required', 'string', 'min:5', 'max:140'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($this->user()->id)],
            'bio' => ['nullable', 'string', 'max:4000'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'education' => ['nullable', 'string', 'max:1000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'is_public' => ['sometimes', 'boolean'],
            'notification_preferences' => ['sometimes', 'array:parent_messages,class_reminders,assignment_submissions'],
            'notification_preferences.parent_messages' => ['sometimes', 'boolean'],
            'notification_preferences.class_reminders' => ['sometimes', 'boolean'],
            'notification_preferences.assignment_submissions' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:3072'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'remove_cover' => ['nullable', 'boolean'],
        ];
    }
}
