<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

final class PeopleDirectoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('academy.view') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => trim($this->input('q'))]);
        }
    }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'cursor' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'sort' => ['sometimes', 'string', Rule::in(['name', 'email', 'joined_at', 'created_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('page') && $this->filled('cursor')) {
                $validator->errors()->add('cursor', 'از page و cursor به‌صورت هم‌زمان استفاده نکنید.');
            }

            $sort = (string) $this->input('sort', 'name');
            if ($this->filled('cursor') && in_array($sort, ['email', 'joined_at'], true)) {
                $validator->errors()->add(
                    'cursor',
                    'برای مرتب‌سازی بر اساس ایمیل یا زمان عضویت از صفحه‌بندی page استفاده کنید؛ این فیلدها ممکن است خالی باشند.'
                );
            }
        });
    }
}
