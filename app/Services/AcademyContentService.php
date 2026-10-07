<?php

namespace App\Services;

use App\Models\AcademyContent;
use Illuminate\Support\Facades\Cache;

class AcademyContentService
{
    public function featuredGroups(int $perGroup = 3): array
    {
        return Cache::remember("public:home:academy-content:{$perGroup}", now()->addMinutes(5), function () use ($perGroup) {
            return AcademyContent::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereHas('category', fn ($q) => $q->where('is_active', true))
                ->with('category:id,title,slug')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->get()
                ->groupBy(fn (AcademyContent $content) => $content->category?->slug)
                ->map(fn ($items) => $items->take($perGroup)->map(fn (AcademyContent $content) => [
                    'title' => $content->title,
                    'excerpt' => $content->excerpt,
                    'type' => $content->type,
                    'duration' => $content->video_duration_seconds
                        ? $this->formatDuration($content->video_duration_seconds)
                        : null,
                    'category' => $content->category?->title,
                    'slug' => $content->slug,
                ])->values()->all())
                ->all();
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
