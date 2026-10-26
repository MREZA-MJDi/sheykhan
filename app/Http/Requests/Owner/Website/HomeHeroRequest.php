<?php

namespace App\Http\Requests\Owner\Website;

use Illuminate\Foundation\Http\FormRequest;

class HomeHeroRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole('seo-specialist')
            && ! $user->hasRole('academy-owner')
            && $user->hasPermission('seo.manage');
    }

    public function rules(): array
    {
        return [
            'slot' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:700'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/|#)/i'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [
                'nullable',
                'prohibited_if:remove_slide,true',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'dimensions:min_width=1200,min_height=600',
                'max:8192',
            ],
            'mobile_image' => [
                'nullable',
                'prohibited_if:remove_slide,true',
                'image',
                'mimes:jpg,jpeg,png,webp,avif',
                'dimensions:min_width=600,min_height=800',
                'max:5120',
            ],
            'crop_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'crop_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'mobile_crop_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'mobile_crop_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'remove_mobile_image' => ['nullable', 'boolean'],
            'remove_slide' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var($this->input('is_active', false), FILTER_VALIDATE_BOOLEAN),
            'remove_mobile_image' => filter_var($this->input('remove_mobile_image', false), FILTER_VALIDATE_BOOLEAN),
            'remove_slide' => filter_var($this->input('remove_slide', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
