<?php

namespace App\Services;

use App\Models\TeacherProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeacherDirectoryService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return TeacherProfile::query()
            ->where('is_verified', true)
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->with([
                'user:id,name',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->withCount([
                'user as courses_count' => fn ($query) => $query
                    ->whereHas('taughtCourses', fn ($courseQuery) => $courseQuery
                        ->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())),
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
