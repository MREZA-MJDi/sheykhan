<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class HomeService
{
    public function __construct(
        private readonly CourseCatalogService $courses,
        private readonly LiveClassService $liveClasses,
        private readonly TeacherDirectoryService $teachers,
        private readonly BlogService $blog,
        private readonly PlatformStatsService $stats,
    ) {
    }

    public function getData(): array
    {
        return Cache::remember('public:home:data:v1', now()->addSeconds(30), fn () => [
            'courseCards' => $this->courses->featuredCards(),
            'liveClassCards' => $this->liveClasses->upcomingCards(),
            'teacherCards' => $this->teachers->featuredCards(),
            'stats' => $this->stats->overview(),
            'latestPosts' => $this->blog->latest(),
        ]);
    }
}
