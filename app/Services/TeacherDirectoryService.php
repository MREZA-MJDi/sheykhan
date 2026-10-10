<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class TeacherDirectoryService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function clearPublicCache(): void
    {
        foreach ([4, 6] as $limit) {
            Cache::forget("public:home:teachers:{$limit}:v2");
        }

        Cache::forget('public:home:data:v3');
        Cache::forget('public:platform:stats:v1');
        Cache::forget('public:seo:sitemap:v1');
    }

    public function findPublic(User $teacher): User
    {
        $teacherId = $teacher->getKey();

        return $this->query()
            ->whereKey($teacherId)
            ->where('status', 'active')
            ->with([
                'teacherProfile:id,user_id,bio,specialization,education,experience_years,is_verified,is_public',
                'teacherProfile.media' => fn ($media) => $media
                    ->wherePivotIn('collection', ['teacher-avatar', 'teacher-cover'])
                    ->where('visibility', 'public')
                    ->where('status', 'active')
                    ->orderByPivot('sort_order'),
                'taughtCourses' => fn ($query) => $query
                    ->published()
                    ->whereHas('academy', fn ($academy) => $academy->where('status', 'active'))
                    ->withCount([
                        'lessons as lessons_count' => fn ($query) => $query
                            ->where('status', 'published')
                            ->where(fn ($query) => $query
                                ->whereNull('published_at')
                                ->orWhere('published_at', '<=', now())
                            ),
                    ])
                    ->with([
                        'media' => fn ($query) => $query
                            ->where('visibility', 'public')
                            ->where('status', 'active')
                            ->orderByPivot('sort_order'),
                    ])
                    ->latest('courses.published_at'),
            ])
            ->firstOrFail();
    }

    public function featuredCards(int $limit = 4): array
    {
        return Cache::remember(
            "public:home:teachers:{$limit}:v2",
            now()->addMinutes(5),
            fn () => $this->query()
                ->latest()
                ->limit($limit)
                ->get(['id', 'name'])
                ->map(fn (User $teacher) => [
                    'name' => $teacher->name,
                    'role' => $teacher->teacherProfile?->specialization ?: 'مدرس',
                    'bio' => $teacher->teacherProfile?->bio,
                    'courses' => $teacher->courses_count,
                    'avatar' => $teacher->teacherProfile?->media->first()?->url(),
                    'href' => route('teachers.show', $teacher),
                ])
                ->all()
        );
    }

    private function query(): Builder
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true)->where('is_public', true))
            ->with([
                'teacherProfile:id,user_id,bio,specialization',
                'teacherProfile.media' => fn ($query) => $query
                    ->wherePivot('collection', 'teacher-avatar')
                    ->where('visibility', 'public')
                    ->where('status', 'active')
                    ->orderByPivot('sort_order'),
            ])
            ->withCount([
                'taughtCourses as courses_count' => fn ($query) => $query
                    ->published()
                    ->whereHas('academy', fn ($academy) => $academy->where('status', 'active')),
            ]);
    }
}
