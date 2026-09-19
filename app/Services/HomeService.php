<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\User;

class HomeService
{
    public function getData(): array
    {
        $courses = Course::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with([
                'academy:id,name',
                'teachers:id,name',
                'sections.lessons:id,course_section_id',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $liveClasses = LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_at', '>=', now()->subHour())
            ->whereHas('course', fn ($query) => $query
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()))
            ->with([
                'course:id,title',
                'teacher:id,name',
            ])
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();

        $teachers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true))
            ->with([
                'teacherProfile.media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->withCount([
                'taughtCourses as courses_count' => fn ($query) => $query
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now()),
            ])
            ->latest()
            ->limit(4)
            ->get(['id', 'name']);

        $stats = [
            'courses' => Course::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'teachers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true))
                ->count(),
            'students' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
                ->count(),
        ];

        $latestPosts = BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'category:id,name',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'category_id', 'title', 'slug', 'excerpt', 'published_at']);

        $courseCards = $courses->map(fn (Course $course) => [
            'title' => $course->title,
            'description' => $course->short_description ?: $course->description,
            'category' => $course->academy?->name,
            'teacher' => $course->teachers->first()?->name,
            'lessons' => $course->sections->sum(fn ($section) => $section->lessons->count()),
            'duration' => $this->formatDuration($course->duration_minutes),
            'price' => $this->formatPrice($course->price),
            'level' => $course->level,
            'image' => $course->media->first()?->url(),
            'href' => route('courses.show', $course),
        ])->all();

        $liveClassCards = $liveClasses->map(fn (LiveClass $class) => [
            'title' => $class->title,
            'course' => $class->course?->title,
            'teacher' => $class->teacher?->name,
            'date' => $this->formatRelativeDate($class->scheduled_at),
            'time' => $class->scheduled_at->format('H:i'),
            'status' => $class->status === 'live' ? 'در حال برگزاری' : 'به‌زودی',
            'href' => $class->course ? route('courses.show', $class->course) : route('courses.index'),
        ])->all();

        $teacherCards = $teachers->map(fn (User $teacher) => [
            'name' => $teacher->name,
            'role' => $teacher->teacherProfile?->specialization ?: 'مدرس',
            'bio' => $teacher->teacherProfile?->bio,
            'courses' => $teacher->courses_count,
            'avatar' => $teacher->teacherProfile?->media->first()?->url(),
        ])->all();

        return compact(
            'courseCards',
            'liveClassCards',
            'teacherCards',
            'stats',
            'latestPosts'
        );
    }

    private function formatRelativeDate(?\Carbon\CarbonInterface $date): string
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

    private function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'مدت زمان متغیر';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours > 0 && $remaining > 0) {
            return $hours . ' ساعت و ' . $remaining . ' دقیقه';
        }

        return $hours > 0
            ? $hours . ' ساعت'
            : $remaining . ' دقیقه';
    }

    private function formatPrice(float|int|string $price): string
    {
        $amount = (float) $price;

        if ($amount <= 0) {
            return 'رایگان';
        }

        return number_format($amount, 0, '.', ',') . ' تومان';
    }
}
