<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Course;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

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
        $urls = collect([
            ['loc' => url('/'), 'lastmod' => now()],
            ['loc' => route('courses.index'), 'lastmod' => now()],
            ['loc' => route('teachers.index'), 'lastmod' => now()],
            ['loc' => route('store.index'), 'lastmod' => now()],
            ['loc' => route('blog.index'), 'lastmod' => now()],
        ]);

        Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->get(['id', 'slug', 'updated_at'])
            ->each(fn (Course $course) => $urls->push([
                'loc' => route('courses.show', $course),
                'lastmod' => $course->updated_at,
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

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}
