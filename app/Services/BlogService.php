<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogService
{
    public function paginate(int $perPage = 9): LengthAwarePaginator
    {
        return BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'category:id,name,slug',
                'author:id,name',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublished(string $slug): BlogPost
    {
        return BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'category:id,name,slug',
                'author:id,name',
                'tags:id,name,slug',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
                'seoMeta',
            ])
            ->firstOrFail();
    }
}
