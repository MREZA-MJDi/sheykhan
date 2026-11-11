<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\AcademyContent;
use App\Models\Course;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

final class SeoController extends Controller
{
    public function robots(): Response
    {
        $sitemap = url('/sitemap.xml');

        return response(
            "User-agent: *\nAllow: /\nDisallow: /owner\nDisallow: /teacher\nDisallow: /student\nDisallow: /parent\nDisallow: /dashboard\nDisallow: /login\nDisallow: /register\nDisallow: /checkout\nDisallow: /orders\nDisallow: /library\n\nSitemap: {$sitemap}\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']
        );
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('public:seo:sitemap:v1', now()->addMinutes(30), function (): string {
            $urls = collect([
            ['loc' => url('/'), 'lastmod' => now()],
            ['loc' => route('courses.index'), 'lastmod' => now()],
            ['loc' => route('teachers.index'), 'lastmod' => now()],
            ['loc' => route('store.index'), 'lastmod' => now()],
            ['loc' => route('blog.index'), 'lastmod' => now()],
        ]);

        User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true)->where('is_public', true))
            ->whereDoesntHave('seoMeta', fn ($query) => $query->where('robots', 'like', 'noindex%'))
            ->with('seoMeta')
            ->get(['id', 'updated_at'])
            ->each(function (User $teacher) use ($urls): void {
                $location = $this->canonicalSitemapLocation(
                    $teacher->seoMeta?->canonical_url,
                    route('teachers.show', $teacher),
                );

                if ($location !== null) {
                    $urls->push(['loc' => $location, 'lastmod' => $teacher->updated_at]);
                }
            });

        Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->whereDoesntHave('seoMeta', fn ($query) => $query->where('robots', 'like', 'noindex%'))
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Course $course) use ($urls): void {
                $location = $this->canonicalSitemapLocation(
                    $course->seoMeta?->canonical_url,
                    route('courses.show', $course),
                );

                if ($location !== null) {
                    $urls->push(['loc' => $location, 'lastmod' => $course->updated_at]);
                }
            });

        AcademyContent::query()
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
            ->whereDoesntHave('seoMeta', fn ($query) => $query->where('robots', 'like', 'noindex%'))
            ->with(['academy:id,slug', 'seoMeta'])
            ->get(['id', 'academy_id', 'slug', 'updated_at'])
            ->each(function (AcademyContent $content) use (&$urls): void {
                if (!$content->academy) {
                    return;
                }

                $location = $this->canonicalSitemapLocation(
                    $content->seoMeta?->canonical_url,
                    route('academy.content.show', [$content->academy, $content]),
                );

                if ($location !== null) {
                    $urls->push(['loc' => $location, 'lastmod' => $content->updated_at]);
                }
            });

        Product::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereDoesntHave('seoMeta', fn ($query) => $query->where('robots', 'like', 'noindex%'))
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (Product $product) use ($urls): void {
                $location = $this->canonicalSitemapLocation(
                    $product->seoMeta?->canonical_url,
                    route('store.product.show', $product),
                );

                if ($location !== null) {
                    $urls->push(['loc' => $location, 'lastmod' => $product->updated_at]);
                }
            });

        BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereDoesntHave('seoMeta', fn ($query) => $query->where('robots', 'like', 'noindex%'))
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->each(function (BlogPost $post) use ($urls): void {
                $location = $this->canonicalSitemapLocation(
                    $post->seoMeta?->canonical_url,
                    route('blog.show', $post->slug),
                );

                if ($location !== null) {
                    $urls->push(['loc' => $location, 'lastmod' => $post->updated_at]);
                }
            });

            $urls = $urls->unique('loc')->values();

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
    /**
     * Sitemap entries must agree with canonical signals. External canonical
     * targets are deliberately omitted: a sitemap must not advertise a URL
     * that tells search engines a different host is authoritative.
     */
    private function canonicalSitemapLocation(?string $canonical, string $fallback): ?string
    {
        if (blank($canonical)) {
            return $fallback;
        }

        $canonical = trim($canonical);
        $canonicalHost = parse_url($canonical, PHP_URL_HOST);
        $siteHost = parse_url(url('/'), PHP_URL_HOST);

        if (! is_string($canonicalHost)
            || ! is_string($siteHost)
            || ! hash_equals(mb_strtolower($siteHost), mb_strtolower($canonicalHost))) {
            return null;
        }

        return $canonical;
    }

}
