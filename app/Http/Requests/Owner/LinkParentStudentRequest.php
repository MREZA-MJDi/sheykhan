<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class LinkParentStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('parents.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['required','integer','exists:users,id'],
            'student_id' => ['required','integer','exists:users,id'],
            'relation' => ['nullable','string','max:100'],
        ];
    }
}
