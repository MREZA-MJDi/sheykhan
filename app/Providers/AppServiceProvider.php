<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
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
        View::composer('components.navigation.navbar', function ($view): void {
            $view->with('navigationGrades', Cache::remember(
                'public:navigation:grades',
                now()->addMinutes(30),
                fn () => AcademicGrade::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get(['id', 'title'])
            ));
        });
    }
}
