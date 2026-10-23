<?php

namespace App\Services;

use App\Models\Academy;
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

        return $this->publicSiteOverview();
    }

    /**
     * SEO specialists see real public URLs across the platform, without access
     * to private user, enrollment, payment, or unpublished content records.
     */
    private function publicSiteOverview(): array
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

        $items = $courses->map(fn (Course $course) => $this->mapItem(
            $course,
            'course',
            $course->title,
            $course->seoMeta,
            $course->academy?->name
        ))->concat(
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
        )->concat(
            $products->map(fn (Product $product) => $this->mapItem(
                $product,
                'product',
                $product->title,
                $product->seoMeta,
                $product->academy?->name
            ))
        )->concat(
            $teachers->map(fn (User $teacher) => $this->mapItem(
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
            'stats' => $this->statsFor($items),
        ];
    }

    public function resolveOwned(User $owner, string $type, int $id): mixed
    {
        $this->authorizeSpecialist($owner);

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

        // Changes to robots directives must be reflected in the sitemap immediately.
        Cache::forget('public:seo:sitemap:v1');
    }

    private function authorizeSpecialist(User $user): void
    {
        abort_unless(
            $user->hasPermission('seo.manage') && $this->isDedicatedSeoSpecialist($user),
            403
        );
    }

    private function isDedicatedSeoSpecialist(User $user): bool
    {
        return $user->hasRole('seo-specialist') && ! $user->hasRole('academy-owner');
    }

    private function statsFor(Collection $items): array
    {
        return [
            'total' => $items->count(),
            'configured' => $items->where('score', '>', 0)->count(),
            'ready' => $items->where('score', '>=', 80)->count(),
            'needsWork' => $items->where('score', '<', 80)->count(),
            'pagesWithFindings' => $items->where('issue_count', '>', 0)->count(),
            'totalFindings' => (int) $items->sum('issue_count'),
            'noindex' => $items->filter(
                fn (array $item): bool => str_starts_with((string) ($item['robots'] ?? ''), 'noindex')
            )->count(),
        ];
    }

    /**
     * Audit stored SEO overrides against the output conventions used by this app.
     * Length ranges are editorial guidance, not a prediction of search rankings.
     */
    private function auditIssues(string $type, ?object $seo): array
    {
        $issues = [];
        $title = trim((string) ($seo?->title ?? ''));
        $description = trim((string) ($seo?->description ?? ''));

        if ($title === '') {
            $issues[] = [
                'severity' => 'warning',
                'title' => 'عنوان SEO اختصاصی تنظیم نشده',
                'detail' => 'عنوان اصلی صفحه به‌عنوان مقدار جایگزین استفاده می‌شود؛ برای کنترل بهتر عنوان نتیجه، یک عنوان توصیفی و یکتا ثبت کن.',
            ];
        } else {
            $length = mb_strlen($title);
            if ($length < 30 || $length > 60) {
                $issues[] = [
                    'severity' => 'info',
                    'title' => 'طول عنوان خارج از بازهٔ راهنمایی است',
                    'detail' => "عنوان فعلی {$length} کاراکتر دارد. بازهٔ ۳۰ تا ۶۰ فقط راهنمای ویرایشی است؛ وضوح و یکتایی عنوان را در اولویت بگذار.",
                ];
            }
        }

        if ($description === '') {
            $issues[] = [
                'severity' => 'warning',
                'title' => 'توضیحات SEO اختصاصی تنظیم نشده',
                'detail' => 'ممکن است قالب از توضیحات صفحه استفاده کند یا توضیحات خالی بماند؛ خروجی HTML را بررسی و توضیحی یکتا ثبت کن.',
            ];
        } else {
            $length = mb_strlen($description);
            if ($length < 70 || $length > 160) {
                $issues[] = [
                    'severity' => 'info',
                    'title' => 'طول توضیحات خارج از بازهٔ راهنمایی است',
                    'detail' => "توضیحات فعلی {$length} کاراکتر دارد. بازهٔ ۷۰ تا ۱۶۰ یک راهنمای نگارشی است و نمایش نهایی snippet تضمین‌شده نیست.",
                ];
            }
        }

        if (blank($seo?->og_image_url)) {
            $issues[] = [
                'severity' => 'info',
                'title' => 'تصویر OG اختصاصی ثبت نشده',
                'detail' => 'در برخی صفحات تصویر رسانهٔ خود محتوا جایگزین می‌شود؛ کارت اشتراک‌گذاری لینک را برای تصویر، برش و تناسب آن بررسی کن.',
            ];
        }

        // Course, blog, product and teacher pages have built-in JSON-LD fallbacks.
        // Public academy content does not currently get the same type-specific fallback.
        if ($type === 'content' && blank($seo?->schema_json)) {
            $issues[] = [
                'severity' => 'info',
                'title' => 'دادهٔ ساختاریافتهٔ اختصاصی تنظیم نشده',
                'detail' => 'برای محتوای مقاله‌ای، در صورت تطابق با محتوای واقعی صفحه، تعریف Article/VideoObject را بررسی کن؛ دادهٔ ساختاریافته نباید اطلاعات ساختگی داشته باشد.',
            ];
        }

        $robots = trim((string) ($seo?->robots ?: 'index,follow'));
        if (str_starts_with($robots, 'noindex')) {
            array_unshift($issues, [
                'severity' => 'critical',
                'title' => 'صفحه روی noindex تنظیم شده',
                'detail' => 'این صفحه از نتایج جستجو کنار گذاشته می‌شود. اگر این تصمیم عمدی نیست، robots را به index,follow تغییر بده.',
            ]);
        }

        return $issues;
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
        $issues = $this->auditIssues($type, $seo);
        $url = match ($type) {
            'course' => route('courses.show', $model),
            'content' => $model->academy ? route('academy.content.show', [$model->academy->slug, $model->slug]) : null,
            'blog' => route('blog.show', $model->slug),
            'product' => route('store.product.show', $model),
            'teacher' => route('teachers.show', $model),
            default => null,
        };

        return [
            'type' => $type,
            'id' => $model->getKey(),
            'label' => $label,
            'academy' => $academy,
            'url' => $url,
            'robots' => $seo?->robots ?: 'index,follow',
            'score' => $score,
            'status' => $score >= 80 ? 'ready' : ($score > 0 ? 'partial' : 'empty'),
            'issues' => $issues,
            'issue_count' => count($issues),
        ];
    }
}
