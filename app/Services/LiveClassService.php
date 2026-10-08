<?php

namespace App\Services;

use App\Models\LiveClass;
use App\Support\PersianUi;
use Illuminate\Support\Facades\Cache;

class LiveClassService
{
    public function upcomingCards(int $limit = 3): array
    {
        return Cache::remember(
            "public:home:live-classes:{$limit}:v1",
            now()->addSeconds(30),
            fn () => LiveClass::query()
                ->whereIn('status', ['scheduled', 'live'])
                ->where('scheduled_at', '>=', now()->subHour())
                ->whereHas('course', fn ($query) => $query->published())
                ->with([
                    'course:id,title',
                    'teacher:id,name',
                ])
                ->orderBy('scheduled_at')
                ->limit($limit)
                ->get()
                ->map(fn (LiveClass $class) => [
                    'title' => $class->title,
                    'course' => $class->course?->title,
                    'teacher' => $class->teacher?->name,
                    'date' => $this->formatDate($class->scheduled_at),
                    'time' => PersianUi::time($class->scheduled_at),
                    'status' => $class->status === 'live' ? 'در حال برگزاری' : 'به‌زودی',
                    'href' => $class->course
                        ? route('courses.show', $class->course)
                        : route('courses.index'),
                ])
                ->all()
        );
    }

    private function formatDate(?\Carbon\CarbonInterface $date): string
    {
        if (!$date) {
            return '-';
        }

        if ($date->isToday()) {
            return 'امروز';
        }

        if ($date->isTomorrow()) {
            return 'فردا';
        }

        return PersianUi::date($date);
    }
}
