<?php

namespace App\Services;

use App\Models\AcademyContent;
use Illuminate\Support\Facades\Cache;

class AcademyContentService
{
    public function featuredGroups(int $perGroup = 3): array
    {
        $slugs = [
            'parents',
            'students',
            'gifted',
            'foreign-resources',
            'question-designer',
        ];

        return Cache::remember("public:home:academy-content:v2:{$perGroup}", now()->addMinutes(5), function () use ($perGroup, $slugs) {
            $groups = [];

            foreach ($slugs as $slug) {
                $groups[$slug] = AcademyContent::query()
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('category', fn ($query) => $query
                        ->where('slug', $slug)
                        ->where('is_active', true))
                    ->with([
                        'category:id,title,slug',
                        'academy:id,name,slug,status',
                        'media' => fn ($query) => $query
                            ->where('visibility', 'public')
                            ->where('status', 'active')
                            ->orderByPivot('sort_order'),
                    ])
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->orderByDesc('published_at')
                    ->limit($perGroup)
                    ->get()
                    ->map(fn (AcademyContent $content) => [
                        'title' => $content->title,
                        'excerpt' => $content->excerpt,
                        'type' => $content->type,
                        'duration' => $content->video_duration_seconds
                            ? $this->formatDuration($content->video_duration_seconds)
                            : null,
                        'category' => $content->category?->title,
                        'slug' => $content->slug,
                        'academy' => $content->academy?->name,
                        'academy_slug' => $content->academy?->slug,
                        'image' => $content->media->first()?->url(),
                        'href' => $content->academy
                            ? route('academy.content.show', [$content->academy, $content])
                            : null,
                    ])
                    ->values()
                    ->all();
            }

            return $groups;
        });
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;

        return $minutes > 0
            ? $minutes . ':' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT)
            : '00:' . str_pad((string) $remaining, 2, '0', STR_PAD_LEFT);
    }
}
