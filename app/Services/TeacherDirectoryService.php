<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeacherDirectoryService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return User::query()
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
            ->paginate($perPage)
            ->withQueryString();
    }
}
