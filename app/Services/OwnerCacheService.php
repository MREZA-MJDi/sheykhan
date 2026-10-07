<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Cache;

final class OwnerCacheService
{
    private const DASHBOARD_TTL = 30;
    private const REPORT_TTL = 60;

    public function dashboardKey(User $owner): string
    {
        return 'owner:dashboard:' . $owner->id;
    }

    public function reportKey(User $owner): string
    {
        return 'owner:report:' . $owner->id;
    }

    public function rememberDashboard(User $owner, Closure $resolver): array
    {
        return Cache::remember(
            $this->dashboardKey($owner),
            now()->addSeconds(self::DASHBOARD_TTL),
            $resolver
        );
    }

    public function rememberReport(User $owner, Closure $resolver): array
    {
        return Cache::remember(
            $this->reportKey($owner),
            now()->addSeconds(self::REPORT_TTL),
            $resolver
        );
    }

    public function forget(User $owner): void
    {
        Cache::forget($this->dashboardKey($owner));
        Cache::forget($this->reportKey($owner));
    }
}
