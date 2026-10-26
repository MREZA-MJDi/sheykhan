<?php

namespace App\Http\Requests\Owner\Content;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContentRequest extends FormRequest
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
            'academy_id' => ['required','integer', Rule::in($this->ownedAcademyIds())],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('academy_content_categories', 'id')
                    ->where('academy_id', (int) $this->input('academy_id'))
                    ->where('is_active', true),
            ],
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
            'video_file' => ['nullable','file','max:204800','mimes:mp4,webm,mov'],
        ];
    }

    private function ownedAcademyIds(): array
    {
        if (! $this->authorize()) {
            return [];
        }

        return \App\Models\Academy::query()
            ->where('status', 'active')
            ->pluck('id')
            ->all();
    }
}
