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
            "User-agent: *\nAllow: /\nDisallow: /owner\nDisallow: /teacher\nDisallow: /student\nDisallow: /parent\nDisallow: /dashboard\nDisallow: /login\nDisallow: /register\n\nSitemap: {$sitemap}\n",
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
            ->get(['id', 'updated_at'])
            ->each(fn (User $teacher) => $urls->push([
                'loc' => route('teachers.show', $teacher),
                'lastmod' => $teacher->updated_at,
            ]));

        Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Course $course) => $urls->push([
                'loc' => route('courses.show', $course),
                'lastmod' => $course->updated_at,
            ]));

        AcademyContent::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('academy:id,slug')
            ->get(['id', 'academy_id', 'slug', 'updated_at'])
            ->each(function (AcademyContent $content) use (&$urls): void {
                if (!$content->academy) {
                    return;
                }

                $urls->push([
                    'loc' => route('academy.content.show', [$content->academy, $content]),
                    'lastmod' => $content->updated_at,
                ]);
            });

        Product::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Product $product) => $urls->push([
                'loc' => route('store.product.show', $product),
                'lastmod' => $product->updated_at,
            ]));

        BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (BlogPost $post) => $urls->push([
                'loc' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at,
            ]));

            return view('seo.sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}
