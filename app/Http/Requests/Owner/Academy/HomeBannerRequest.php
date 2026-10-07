<?php

namespace App\Http\Requests\Owner\Academy;

use Illuminate\Foundation\Http\FormRequest;

class HomeBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('academy.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'banners' => ['array'],
            'banners.*.media_id' => ['nullable', 'integer', 'exists:media,id'],
            'banners.*.title' => ['nullable', 'string', 'max:255'],
            'banners.*.description' => ['nullable', 'string', 'max:700'],
            'banners.*.cta_label' => ['nullable', 'string', 'max:80'],
            'banners.*.cta_url' => ['nullable', 'string', 'max:2048'],
            'banners.*.is_active' => ['nullable', 'boolean'],
            'banners.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $banners = $this->input('banners', []);

        foreach ($banners as $slot => &$banner) {
            $banner['is_active'] = filter_var($banner['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        $this->merge(['banners' => $banners]);
    }
}
