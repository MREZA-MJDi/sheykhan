<?php

namespace App\Http\Requests\Owner\Seo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('seo.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:1500'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'robots' => ['required', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image_url' => ['nullable', 'url', 'max:2048'],
            'schema_json' => ['nullable', 'json', 'max:20000'],
        ];
    }
}
