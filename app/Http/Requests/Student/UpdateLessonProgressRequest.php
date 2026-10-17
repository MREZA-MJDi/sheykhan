<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') && $this->user()?->hasPermission('lessons.progress');
    }

    public function rules(): array
    {
        // The browser reports an event, never percent/watched-second totals.
        // Progress totals are derived on the server.
        return [
            'event' => ['required', Rule::in(['heartbeat', 'playback_completed', 'mark_read'])],
        ];
    }

    public function messages(): array
    {
        return [
            'event.required' => 'رویداد پیشرفت درس مشخص نشده است.',
            'event.in' => 'نوع رویداد پیشرفت معتبر نیست.',
        ];
    }
}
