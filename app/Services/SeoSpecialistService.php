<?php

namespace App\Services;

use App\Models\AcademyContent;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class SeoSpecialistService
{
    public function __construct(
        private readonly SeoPageSummary $summary,
        private readonly SeoMetadataWriter $writer,
    ) {
    }

    public function overview(User $specialist): array
    {
        $this->authorizeDedicatedSpecialist($specialist);

        return $this->publicOverview();
    }

    private function publicOverview(): array
    {
        $courses = Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with(['academy:id,name', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->get(['id', 'academy_id', 'title', 'slug', 'status', 'published_at', 'updated_at']);

        $contents = AcademyContent::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->where(function ($query): void {
                $query->where('type', 'article')
                    ->orWhere(function ($video): void {
                        $video->where('type', 'video')
                            ->whereHas('media', fn ($media) => $media
                                ->where('media_attachments.collection', 'video')
                                ->where('media.collection', 'video')
                                ->where('media.visibility', 'public')
                                ->where('media.status', 'active'));
                    });
            })
            ->with(['academy:id,name,slug', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->get(['id', 'academy_id', 'category_id', 'title', 'slug', 'type', 'status', 'published_at', 'updated_at']);

        $posts = BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('seoMeta')
            ->orderByDesc('updated_at')
            ->get(['id', 'category_id', 'author_id', 'title', 'slug', 'status', 'published_at', 'updated_at']);

        $products = Product::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['academy:id,name', 'seoMeta'])
            ->orderByDesc('updated_at')
            ->get(['id', 'academy_id', 'category_id', 'title', 'slug', 'status', 'published_at', 'updated_at']);

        $teachers = User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
            ->whereHas('teacherProfile', fn ($query) => $query
                ->where('is_verified', true)
                ->where('is_public', true))
            ->with(['seoMeta', 'teacherProfile'])
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'updated_at']);

        $items = $courses->map(fn (Course $course) => $this->summary->mapItem(
            $course,
            'course',
            $course->title,
            $course->seoMeta,
            $course->academy?->name
        ))->concat(
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
        )->concat(
            $teachers->map(fn (User $teacher) => $this->summary->mapItem(
                $teacher,
                'teacher',
                $teacher->name,
                $teacher->seoMeta
            ))
        )->values();

        return [
            'academies' => collect(),
            'courses' => collect(),
            'contents' => collect(),
            'posts' => collect(),
            'products' => collect(),
            'items' => $items,
            'isGlobal' => true,
            'stats' => $this->summary->statsFor($items),
        ];
    }

    public function resolvePublic(User $specialist, string $type, int $id): Model
    {
        $this->authorizeDedicatedSpecialist($specialist);

        return match ($type) {
                'course' => Course::query()
                    ->whereKey($id)
                    ->published()
                    ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                    ->firstOrFail(),

                'content' => AcademyContent::query()
                    ->whereKey($id)
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                    ->whereHas('category', fn ($query) => $query->where('is_active', true))
                    ->where(function ($query): void {
                        $query->where('type', 'article')
                            ->orWhere(function ($video): void {
                                $video->where('type', 'video')
                                    ->whereHas('media', fn ($media) => $media
                                        ->where('media_attachments.collection', 'video')
                                        ->where('media.collection', 'video')
                                        ->where('media.visibility', 'public')
                                        ->where('media.status', 'active'));
                            });
                    })
                    ->firstOrFail(),

                'blog' => BlogPost::query()
                    ->whereKey($id)
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->firstOrFail(),

                'product' => Product::query()
                    ->whereKey($id)
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('category', fn ($query) => $query->where('is_active', true))
                    ->firstOrFail(),

                'teacher' => User::query()
                    ->whereKey($id)
                    ->where('status', 'active')
                    ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                    ->whereHas('teacherProfile', fn ($query) => $query
                        ->where('is_verified', true)
                        ->where('is_public', true))
                    ->firstOrFail(),

                default => abort(404),
            };
    }

    public function update(User $specialist, string $type, int $id, array $data): void
    {
        $this->writer->update($this->resolvePublic($specialist, $type, $id), $data);
    }

    private function authorizeDedicatedSpecialist(User $user): void
    {
        abort_unless(
            $user->hasRole('seo-specialist')
            && ! $user->hasRole('academy-owner')
            && $user->hasPermission('seo.manage'),
            403,
        );
    }
}
