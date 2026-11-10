<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\MobileNumber;
use App\Models\Academy;
use Illuminate\Validation\Validator;

final class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->hasRole('academy-owner') ?? false)
            && ($this->user()?->hasPermission('parents.manage') ?? false);
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
            'student_id' => ['required', 'integer', 'min:1'],
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'name' => ['required', 'string', 'min:5', 'max:140'],
            'mobile' => ['required', 'regex:/^09[0-9]{9}$/'],
            'relation' => ['required', 'string', Rule::in(['مادر', 'پدر', 'سرپرست', 'سایر'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $academy = $this->route('academy');
            if (! $academy instanceof Academy || ! $this->filled('student_id') || $validator->errors()->has('student_id')) {
                return;
            }

            $isActiveAcademyStudent = $academy->users()
                ->wherePivot('role', 'student')
                ->wherePivot('status', 'active')
                ->where('users.status', 'active')
                ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                ->where('users.id', (int) $this->input('student_id'))
                ->exists();

            if (! $isActiveAcademyStudent) {
                $validator->errors()->add(
                    'student_id',
                    'دانش‌آموز انتخاب‌شده فعال نیست یا به این آموزشگاه تعلق ندارد؛ دوباره از فهرست انتخاب کن.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'دانش‌آموز را انتخاب کنید.',
            'first_name.required' => 'نام والد را وارد کنید.',
            'last_name.required' => 'نام خانوادگی والد را وارد کنید.',
            'mobile.required' => 'شماره موبایل والد را وارد کنید.',
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'relation.required' => 'نوع ارتباط را انتخاب کنید.',
        ];
    }
}
