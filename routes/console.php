<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('sheykhan:seo-search-console-sync --days=28')
    ->dailyAt('04:20')
    ->withoutOverlapping()
    ->when(fn (): bool => filled(config('services.google_search_console.site_url'))
        && filled(config('services.google_search_console.client_id'))
        && filled(config('services.google_search_console.client_secret'))
        && filled(config('services.google_search_console.refresh_token')))
    ->description('Refresh recent organic page and query performance from Google Search Console');
