<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;

class PlatformStatsService
{
    public function public(): array
    {
        return [
            'courses' => Course::query()->published()->count(),
            'teachers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true))
                ->count(),
            'students' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
                ->count(),
        ];
    }
}
