<?php

namespace App\Http\Requests\Owner\Website;

use Illuminate\Foundation\Http\FormRequest;

class HomeHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('academy.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'image' => [
                'nullable',
                'prohibited_if:restore_default,true',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'dimensions:min_width=1200,min_height=600',
                'max:8192',
            ],
            'mobile_image' => [
                'nullable',
                'prohibited_if:restore_default,true',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'dimensions:min_width=600,min_height=800',
                'max:5120',
            ],
            'crop_x' => ['required', 'integer', 'min:0', 'max:100'],
            'crop_y' => ['required', 'integer', 'min:0', 'max:100'],
            'mobile_crop_x' => ['required', 'integer', 'min:0', 'max:100'],
            'mobile_crop_y' => ['required', 'integer', 'min:0', 'max:100'],
            'remove_mobile_image' => ['nullable', 'boolean'],
            'restore_default' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_mobile_image' => filter_var($this->input('remove_mobile_image', false), FILTER_VALIDATE_BOOLEAN),
            'restore_default' => filter_var($this->input('restore_default', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
