<?php

namespace App\Providers;

use App\Auth\LegacyAwareUserProvider;
use App\Models\AcademicGrade;
use App\Models\Media;
use App\Policies\MediaPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('legacy-aware-eloquent', function ($app, array $config) {
            return new LegacyAwareUserProvider($app['hash'], $config['model']);
        });

        Gate::policy(Media::class, MediaPolicy::class);

        View::composer('components.navigation.navbar', function ($view): void {
            $cacheKey = 'public:navigation:grades';
            $navigationGrades = null;

            try {
                $navigationGrades = Cache::get($cacheKey);
            } catch (\Throwable) {
                // Public navigation must remain available when the cache store
                // is down. The database query below remains the source of truth.
            }

            if (!($navigationGrades instanceof Collection)) {
                $query = AcademicGrade::query()->where('is_active', true);

                if ($query->getConnection()->getDriverName() === 'sqlite') {
                    $query->orderByRaw(
                        "CASE WHEN code GLOB '[0-9]*' AND code NOT GLOB '*[^0-9]*' THEN CAST(code AS INTEGER) ELSE 999999 END"
                    );
                } else {
                    $query->orderByRaw(
                        "CASE WHEN code REGEXP '^[0-9]+$' THEN CAST(code AS UNSIGNED) ELSE 999999 END"
                    );
                }

                // Let genuine database errors surface instead of hiding them as
                // an empty menu or retrying a failed source query.
                $navigationGrades = $query
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'title']);

                try {
                    Cache::put($cacheKey, $navigationGrades, now()->addMinutes(30));
                } catch (\Throwable) {
                    // This cache is optional; a miss can safely be recomputed.
                }
            }

            $view->with('navigationGrades', $navigationGrades);
        });
    }
}
