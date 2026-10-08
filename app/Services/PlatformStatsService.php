<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PlatformStatsService
{
    public function overview(): array
    {
        return Cache::remember('public:platform:stats:v1', now()->addMinutes(5), fn () => [
            'courses' => Course::query()->published()->count(),
            'teachers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true))
                ->count(),
            'students' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
                ->count(),
        ]);
    }
}
