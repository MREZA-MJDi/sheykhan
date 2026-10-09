<?php

namespace App\Services;

use App\Models\Achievement;
use Illuminate\Support\Facades\Cache;

class AchievementService
{
    public function featured(int $limit = 6): array
    {
        return Cache::remember("public:home:achievements:{$limit}", now()->addMinutes(10), fn () =>
            Achievement::query()
                ->where('status', 'published')
                ->where('is_featured', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with([
                    'media' => fn ($query) => $query
                        ->where('visibility', 'public')
                        ->where('status', 'active'),
                    'grade:id,title',
                ])
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Achievement $achievement) => [
                    'name' => $achievement->display_name,
                    'school' => $achievement->school_name,
                    'type' => $achievement->achievement_type,
                    'title' => $achievement->title,
                    'image' => $achievement->media?->url(),
                    'grade' => $achievement->grade?->title,
                ])
                ->all()
        );
    }
}
