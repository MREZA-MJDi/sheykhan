<?php

namespace App\Services;

use App\Models\LiveClass;

class LiveClassService
{
    public function upcomingCards(int $limit = 3): array
    {
        return LiveClass::query()
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
                'time' => $class->scheduled_at->format('H:i'),
                'status' => $class->status === 'live' ? 'در حال برگزاری' : 'به‌زودی',
                'href' => $class->course
                    ? route('courses.show', $class->course)
                    : route('courses.index'),
            ])
            ->all();
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

        return $date->format('Y/m/d');
    }
}
