<?php

namespace App\Services;

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
        return [
            'courseCards' => $this->courses->featuredCards(),
            'liveClassCards' => $this->liveClasses->upcomingCards(),
            'teacherCards' => $this->teachers->featuredCards(),
            'stats' => $this->stats->public(),
            'latestPosts' => $this->blog->latest(),
        ];
    }
}
