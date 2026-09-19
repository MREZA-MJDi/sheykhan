<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class BlogService
{
    public function paginate(int $perPage = 9): LengthAwarePaginator
    {
        return $this->publishedQuery()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function latest(int $limit = 3): Collection
    {
        return $this->publishedQuery()
            ->latest('published_at')
            ->limit($limit)
            ->get(['id', 'category_id', 'title', 'slug', 'excerpt', 'published_at']);
    }

    public function findPublished(string $slug): BlogPost
    {
        return $this->publishedQuery()
            ->where('slug', $slug)
            ->with([
                'tags:id,name,slug',
                'seoMeta',
            ])
            ->firstOrFail();
    }

    private function publishedQuery(): Builder
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
            ]);
    }
}
