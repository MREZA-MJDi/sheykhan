<?php

namespace App\Http\Requests\Teacher;

use App\Support\PersianUi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawDate = $this->input('attendance_date');

        $this->merge([
            'attendance_date' => PersianUi::normalizeDate(
                is_string($rawDate) ? $rawDate : null
            ),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('attendance.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'attendance_date' => ['required', 'date_format:Y-m-d'],
            'attendance' => ['required', 'array'],
            'attendance.*' => [Rule::in(['present', 'absent', 'late', 'excused'])],
        ];
    }
}
