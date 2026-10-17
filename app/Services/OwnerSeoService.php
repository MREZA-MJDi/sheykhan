<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;

final class OwnerSeoService
{
    public function __construct(
        private readonly SeoPageSummary $summary,
        private readonly SeoMetadataWriter $writer,
    ) {
    }

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
            ->with(['academy:id,name,slug', 'seoMeta'])
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

        $items = $academies->map(fn (Academy $academy) => $this->summary->mapItem(
            $academy,
            'academy',
            $academy->name,
            $academy->seoMeta
        ));

        $items = $items->concat(
            $courses->map(fn (Course $course) => $this->summary->mapItem(
                $course,
                'course',
                $course->title,
                $course->seoMeta,
                $course->academy?->name
            ))
        )->concat(
            $contents->map(fn (AcademyContent $content) => $this->summary->mapItem(
                $content,
                'content',
                $content->title,
                $content->seoMeta,
                $content->academy?->name
            ))
        )->concat(
            $posts->map(fn (BlogPost $post) => $this->summary->mapItem(
                $post,
                'blog',
                $post->title,
                $post->seoMeta
            ))
        )->concat(
            $products->map(fn (Product $product) => $this->summary->mapItem(
                $product,
                'product',
                $product->title,
                $product->seoMeta,
                $product->academy?->name
            ))
        );

        $items = $items->values();

        return [
            'academies' => $academies,
            'courses' => $courses,
            'contents' => $contents,
            'posts' => $posts,
            'products' => $products,
            'items' => $items,
            'isGlobal' => false,
            'stats' => $this->summary->statsFor($items),
        ];
    }

    /**
     * SEO specialists see real public URLs across the platform, without access
     * to private user, enrollment, payment, or unpublished content records.
     */
    public function resolveOwned(User $owner, string $type, int $id): mixed
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
        $this->writer->update($this->resolveOwned($owner, $type, $id), $data);
    }
}
