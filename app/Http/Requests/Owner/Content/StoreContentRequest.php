<?php

namespace App\Http\Requests\Owner\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'academy_id' => ['required','integer', Rule::in($this->ownedAcademyIds())],
            'category_id' => ['required','integer','exists:academy_content_categories,id'],
            'type' => ['required', Rule::in(['article','video'])],
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','alpha_dash'],
            'excerpt' => ['nullable','string','max:500'],
            'body' => ['nullable','string'],
            'video_duration_seconds' => ['nullable','integer','min:1','max:86400'],
            'status' => ['sometimes', Rule::in(['draft','published','archived'])],
            'is_featured' => ['sometimes','boolean'],
            'sort_order' => ['sometimes','integer','min:0','max:100000'],
            'published_at' => ['nullable','date'],
            'cover_image' => ['nullable','file','max:10240','mimes:jpg,jpeg,png,webp'],
        ];
    }

    private function ownedAcademyIds(): array
    {
        return $this->user()
            ?->ownedAcademies()
            ->where('status', 'active')
            ->pluck('id')
            ->all() ?? [];
    }
}
