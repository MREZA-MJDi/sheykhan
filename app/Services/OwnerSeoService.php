<?php

namespace App\Services;

use App\Models\AcademyContent;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class OwnerSeoService
{
    public function overview(User $owner): array
    {
        $this->authorizeSpecialist($owner);

        return Cache::remember(
            'owner-seo:editorial-overview:v1',
            now()->addMinute(),
            fn (): array => $this->publicSiteOverview(),
        );
    }

    /**
     * The SEO console intentionally covers editorial pages only: published
     * blog posts and academy articles. Each source is eager-loaded and capped
     * to a practical recent window; the editor does not scan every catalog row.
     */
    private function publicSiteOverview(): array
    {
        $posts = BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('seoMeta')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get(['id', 'title', 'slug', 'status', 'published_at', 'updated_at']);

        $articles = AcademyContent::query()
            ->where('type', 'article')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['academy:id,name,slug', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get(['id', 'academy_id', 'category_id', 'title', 'slug', 'status', 'published_at', 'updated_at']);

        $items = $posts->map(fn (BlogPost $post) => $this->mapItem(
            $post,
            'blog',
            $post->title,
            $post->seoMeta,
        ))->concat(
            $articles->map(fn (AcademyContent $article) => $this->mapItem(
                $article,
                'content',
                $article->title,
                $article->seoMeta,
                $article->academy?->name,
            ))
        )->sortByDesc('updated_at')->values();

        return [
            'items' => $items,
            'stats' => [
                'total' => $items->count(),
                'indexable' => $items->filter(fn (array $item) => ! str_starts_with($item['robots'], 'noindex'))->count(),
                'noindex' => $items->filter(fn (array $item) => str_starts_with($item['robots'], 'noindex'))->count(),
            ],
            'scannedAt' => now(),
            'latestContentUpdateAt' => $items->pluck('updated_at')->filter()->sortDesc()->first(),
        ];
    }

    public function resolveOwned(User $owner, string $type, int $id): mixed
    {
        $this->authorizeSpecialist($owner);

        return match ($type) {
            'content' => AcademyContent::query()
                ->whereKey($id)
                ->where('type', 'article')
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->firstOrFail(),

            'blog' => BlogPost::query()
                ->whereKey($id)
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->firstOrFail(),

            // Legacy routes remain supported for existing SEO metadata links.
            'course' => Course::query()
                ->whereKey($id)
                ->published()
                ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                ->firstOrFail(),

            'product' => Product::query()
                ->whereKey($id)
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->firstOrFail(),

            default => abort(404),
        };
    }

    public function update(User $owner, string $type, int $id, array $data): void
    {
        $model = $this->resolveOwned($owner, $type, $id);
        $current = $model->seoMeta;

        $payload = [
            'title' => $data['title'] ?? $current?->title,
            'description' => $data['description'] ?? $current?->description,
            'keywords' => $current?->keywords,
            'canonical_url' => $current?->canonical_url,
            'robots' => $data['robots'] ?? $current?->robots ?? 'index,follow',
            'og_title' => $current?->og_title,
            'og_description' => $current?->og_description,
            'og_image_url' => $current?->og_image_url,
            'schema_json' => $current?->schema_json,
        ];

        $model->seoMeta()->updateOrCreate([], $payload);

        Cache::forget('public:seo:sitemap:v1');
        Cache::forget('owner-seo:editorial-overview:v1');
    }

    private function mapItem(object $model, string $type, string $label, ?object $seo, ?string $academy = null): array
    {
        return [
            'type' => $type,
            'id' => (int) $model->getKey(),
            'label' => $label,
            'academy' => $academy,
            'url' => $type === 'blog'
                ? route('blog.show', $model->slug)
                : ($model->academy ? route('academy.content.show', [$model->academy->slug, $model->slug]) : null),
            'robots' => $seo?->robots ?: 'index,follow',
            'seo_title' => $seo?->title,
            'seo_description' => $seo?->description,
            'updated_at' => $model->getAttribute('updated_at'),
            'published_at' => $model->getAttribute('published_at'),
        ];
    }

    private function authorizeSpecialist(User $user): void
    {
        abort_unless(
            $user->hasPermission('seo.manage') && $user->hasRole('seo-specialist') && ! $user->hasRole('academy-owner'),
            403,
        );
    }
}
