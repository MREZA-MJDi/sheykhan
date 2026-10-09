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
            'courses' => Course::query()
                ->published()
                ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                ->count(),
            'teachers' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                ->whereHas('teacherProfile', fn ($query) => $query->where('is_verified', true)->where('is_public', true))
                ->count(),
            'students' => User::query()
                ->where('status', 'active')
                ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
                ->count(),
        ]);
    }
}
