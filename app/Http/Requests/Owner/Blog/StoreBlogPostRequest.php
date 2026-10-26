<?php

namespace App\Http\Requests\Owner\Blog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->hasRole('seo-specialist')
            && ! $user->hasRole('academy-owner')
            && $user->hasPermission('seo.manage');
    }
    public function rules(): array {
        return [
            'category_id'=>['nullable','integer','exists:blog_categories,id'],
            'title'=>['required','string','max:255'],
            'slug'=>['nullable','string','max:255','alpha_dash'],
            'excerpt'=>['nullable','string','max:500'],
            'content'=>['required','string'],
            'status'=>['sometimes',Rule::in(['draft','published'])],
            'published_at'=>['nullable','date'],
            'cover_image'=>['nullable','file','max:10240','mimes:jpg,jpeg,png,webp'],
        ];
    }
}
