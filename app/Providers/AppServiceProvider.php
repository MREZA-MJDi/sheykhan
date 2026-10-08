<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\Media;
use App\Policies\MediaPolicy;
use App\Models\AcademicGrade;

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
        Gate::policy(Media::class, MediaPolicy::class);

        View::composer('components.navigation.navbar', function ($view): void {
            $view->with('navigationGrades', Cache::remember(
                'public:navigation:grades',
                now()->addMinutes(30),
                function () {
                    $query = AcademicGrade::query()
                        ->where('is_active', true);

                    if ($query->getConnection()->getDriverName() === 'sqlite') {
                        $query->orderByRaw(
                            "CASE WHEN code GLOB '[0-9]*' AND code NOT GLOB '*[^0-9]*' THEN CAST(code AS INTEGER) ELSE 999999 END"
                        );
                    } else {
                        $query->orderByRaw(
                            "CASE WHEN code REGEXP '^[0-9]+$' THEN CAST(code AS UNSIGNED) ELSE 999999 END"
                        );
                    }

                    return $query
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get(['id', 'title']);
                }
            ));
        });
    }
}
