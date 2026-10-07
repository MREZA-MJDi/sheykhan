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
                ->with(['media:id,disk,path,visibility', 'grade:id,name'])
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Achievement $achievement) => [
                    'name' => $achievement->display_name,
                    'school' => $achievement->school_name,
                    'type' => $achievement->achievement_type,
                    'title' => $achievement->title,
                    'image' => $achievement->media?->url(),
                    'grade' => $achievement->grade?->name,
                ])
                ->all()
        );
    }
}
