<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('attendance.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*' => [Rule::in(['present', 'absent', 'late', 'excused'])],
        ];
    }
}
