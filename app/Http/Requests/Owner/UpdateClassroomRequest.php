<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends StoreClassroomRequest
{
    public function rules(): array
    {
        $academy = $this->route('academy');
        $classroomId = $this->route('classroom');

        return [
            ...parent::rules(),
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classrooms', 'code')
                    ->where(fn ($query) => $query->where('academy_id', $academy?->id))
                    ->ignore($classroomId),
            ],
        ];
    }
}
