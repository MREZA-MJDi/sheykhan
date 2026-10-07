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
        private readonly ProductCatalogService $products,
        private readonly AcademyContentService $academyContent,
        private readonly AchievementService $achievements,
        private readonly TestimonialService $testimonials,
    ) {
    }

    public function getData(): array
    {
        return Cache::remember('public:home:data:v2', now()->addSeconds(30), fn () => [
            'courseCards' => $this->courses->featuredCards(),
            'liveClassCards' => $this->liveClasses->upcomingCards(),
            'teacherCards' => $this->teachers->featuredCards(),
            'stats' => $this->stats->overview(),
            'latestPosts' => $this->blog->latest(),
            'storeCategories' => $this->products->categories(),
            'productCards' => $this->products->featuredCards(),
            'academyContentGroups' => $this->academyContent->featuredGroups(),
            'achievementCards' => $this->achievements->featured(),
            'testimonialCards' => $this->testimonials->featured(),
        ]);
    }
}
