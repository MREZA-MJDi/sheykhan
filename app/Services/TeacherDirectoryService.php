<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TeacherDirectoryService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->paginate($perPage)
            ->withQueryString();
    }


    public function findPublic(User $teacher): User
    {
        return $this->query()
            ->whereKey($teacher->id)
            ->where('status', 'active')
            ->with([
                'taughtCourses' => fn ($query) => $query
                    ->published()
                    ->withCount('lessons')
                    ->latest('courses.published_at'),
            ])
            ->firstOrFail();
    }

    public function featuredCards(int $limit = 4): array
    {
        return $this->query()
            ->latest()
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn (User $teacher) => [
                'name' => $teacher->name,
                'role' => $teacher->teacherProfile?->specialization ?: 'مدرس',
                'bio' => $teacher->teacherProfile?->bio,
                'courses' => $teacher->courses_count,
                'avatar' => $teacher->teacherProfile?->media->first()?->url(),
            ])
            ->all();
    }

    private function query(): Builder
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
            ->whereHas('academies', fn ($query) => $query
                ->where('academy_user.status', 'active')
                ->where('academy_user.role', 'teacher')
                ->where('academies.status', 'active'))
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true)->where('is_public', true))
            ->with([
                'teacherProfile:id,user_id,bio,specialization',
                'teacherProfile.media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->withCount([
                'taughtCourses as courses_count' => fn ($query) => $query->published(),
            ]);
    }
}
