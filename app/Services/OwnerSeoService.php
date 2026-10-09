<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Course;
use App\Models\AcademyContent;
use App\Models\BlogPost;
use App\Models\User;

final class OwnerSeoService
{
    public function overview(User $owner): array
    {
        $academies = $owner->ownedAcademies()
            ->where('status', 'active')
            ->with('seoMeta')
            ->orderBy('name')
            ->get();

        $courses = Course::query()
            ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
            ->with(['academy:id,name', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $contents = AcademyContent::query()
            ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
            ->with(['academy:id,name', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $posts = BlogPost::query()
            ->where('author_id', $owner->id)
            ->with('seoMeta')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $products = Product::query()
            ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
            ->with(['academy:id,name', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $items = $academies->map(fn (Academy $academy) => $this->mapItem(
            $academy,
            'academy',
            $academy->name,
            $academy->seoMeta
        ));

        $items = $items->concat(
            $courses->map(fn (Course $course) => $this->mapItem(
                $course,
                'course',
                $course->title,
                $course->seoMeta,
                $course->academy?->name
            ))
        )->concat(
            $contents->map(fn (AcademyContent $content) => $this->mapItem(
                $content,
                'content',
                $content->title,
                $content->seoMeta,
                $content->academy?->name
            ))
        )->concat(
            $posts->map(fn (BlogPost $post) => $this->mapItem(
                $post,
                'blog',
                $post->title,
                $post->seoMeta
            ))
        );

        $items = $items->concat(
            $products->map(fn (Product $product) => $this->mapItem(
                $product,
                'product',
                $product->title,
                $product->seoMeta,
                $product->academy?->name
            ))
        );

        return [
            'academies' => $academies,
            'courses' => $courses,
            'contents' => $contents,
            'posts' => $posts,
            'products' => $products,
            'items' => $items->values(),
            'stats' => [
                'total' => $items->count(),
                'configured' => $items->where('score', '>', 0)->count(),
                'ready' => $items->where('score', '>=', 80)->count(),
                'needsWork' => $items->where('score', '<', 80)->count(),
            ],
        ];
    }

    public function resolveOwned(User $owner, string $type, int $id)
    {
        return match ($type) {
            'academy' => Academy::query()
                ->whereKey($id)
                ->where('owner_id', $owner->id)
                ->firstOrFail(),

            'course' => Course::query()
                ->whereKey($id)
                ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
                ->firstOrFail(),

            'content' => AcademyContent::query()
                ->whereKey($id)
                ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
                ->firstOrFail(),

            'product' => Product::query()
                ->whereKey($id)
                ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id))
                ->firstOrFail(),

            'blog' => BlogPost::query()
                ->whereKey($id)
                ->where('author_id', $owner->id)
                ->firstOrFail(),

            default => abort(404),
        };
    }

    public function update(User $owner, string $type, int $id, array $data): void
    {
        $model = $this->resolveOwned($owner, $type, $id);

        $payload = [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'robots' => $data['robots'] ?? 'index,follow',
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image_url' => $data['og_image_url'] ?? null,
            'schema_json' => filled($data['schema_json'] ?? null)
                ? json_decode($data['schema_json'], true, 512, JSON_THROW_ON_ERROR)
                : null,
        ];

        $model->seoMeta()->updateOrCreate([], $payload);
    }

    private function mapItem(
        object $model,
        string $type,
        string $label,
        ?object $seo,
        ?string $academy = null
    ): array {
        $fields = [
            'title' => $seo?->title,
            'description' => $seo?->description,
            'canonical_url' => $seo?->canonical_url,
            'og_title' => $seo?->og_title,
            'og_description' => $seo?->og_description,
            'og_image_url' => $seo?->og_image_url,
            'schema_json' => $seo?->schema_json,
        ];

        $filled = collect($fields)->filter(fn ($value) => filled($value))->count();
        $score = (int) round(($filled / count($fields)) * 100);

        return [
            'type' => $type,
            'id' => $model->getKey(),
            'label' => $label,
            'academy' => $academy,
            'score' => $score,
            'status' => $score >= 80 ? 'ready' : ($score > 0 ? 'partial' : 'empty'),
            'meta' => $seo,
        ];
    }
}
