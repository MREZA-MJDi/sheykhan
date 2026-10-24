<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Cache;
use Throwable;

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
        return $this->rememberSafely(
            $this->dashboardKey($owner),
            self::DASHBOARD_TTL,
            $resolver,
        );
    }

    public function rememberReport(User $owner, Closure $resolver): array
    {
        return $this->rememberSafely(
            $this->reportKey($owner),
            self::REPORT_TTL,
            $resolver,
        );
    }

    /**
     * Dashboard data must remain available when an optional cache backend is
     * unavailable. Cache reads/writes are isolated from the resolver so real
     * application/database exceptions are not swallowed or retried as cache
     * misses.
     */
    private function rememberSafely(string $key, int $ttlSeconds, Closure $resolver): array
    {
        try {
            $cached = Cache::get($key);

            if (is_array($cached)) {
                return $cached;
            }
        } catch (Throwable) {
            // Fall through to the source of truth if the cache is unavailable.
        }

        $value = $resolver();

        try {
            Cache::put($key, $value, now()->addSeconds($ttlSeconds));
        } catch (Throwable) {
            // Caching is an optimization. A cache outage must not break a page.
        }

        return $value;
    }

    public function forget(User $owner): void
    {
        foreach ([$this->dashboardKey($owner), $this->reportKey($owner)] as $key) {
            try {
                Cache::forget($key);
            } catch (Throwable) {
                // A successful write should not become a 500 just because the
                // optional cache invalidation backend is temporarily offline.
            }
        }
    }
}
