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
        $merge = [];
        if ($this->exists('first_name') || $this->exists('last_name')) {
            $currentName = (string) ($this->user()?->name ?? '');
            $firstName = trim((string) $this->input('first_name', \\Illuminate\\Support\\Str::before($currentName, ' ')));
            $lastName = trim((string) $this->input('last_name', \\Illuminate\\Support\\Str::after($currentName, ' ')));
            $merge['first_name'] = $firstName;
            $merge['last_name'] = $lastName;
            $merge['name'] = trim($firstName . ' ' . $lastName);
        }
        if ($this->exists('mobile')) {
            $merge['mobile'] = MobileNumber::normalize((string) $this->input('mobile'));
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'min:2', 'max:60'],
            'last_name' => ['sometimes', 'required', 'string', 'min:2', 'max:80'],
            'name' => ['sometimes', 'required', 'string', 'min:5', 'max:140'],
            'mobile' => ['sometimes', 'required', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'mobile')->ignore($this->user()->id)],
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
