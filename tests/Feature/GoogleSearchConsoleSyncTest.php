<?php

namespace Tests\Feature;

use App\Services\GoogleSearchConsoleSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleSearchConsoleSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google_search_console.site_url' => 'https://sheykhan.test/',
            'services.google_search_console.client_id' => 'oauth-client-id',
            'services.google_search_console.client_secret' => 'oauth-client-secret',
            'services.google_search_console.refresh_token' => 'oauth-refresh-token',
        ]);
    }

    public function test_sync_refreshes_oauth_and_persists_page_and_page_query_performance(): void
    {
        Http::fakeSequence()
            ->push(['access_token' => 'temporary-access-token', 'expires_in' => 3600], 200)
            ->push([
                'rows' => [[
                    'keys' => ['https://sheykhan.test/courses/math', '2026-11-01'],
                    'clicks' => 17,
                    'impressions' => 340,
                    'ctr' => 0.05,
                    'position' => 8.4,
                ]],
            ], 200)
            ->push([
                'rows' => [[
                    'keys' => ['https://sheykhan.test/courses/math', 'کلاس آنلاین ریاضی', '2026-11-01'],
                    'clicks' => 7,
                    'impressions' => 120,
                    'ctr' => 0.058333,
                    'position' => 6.2,
                ]],
            ], 200);

        $result = app(GoogleSearchConsoleSyncService::class)->sync(
            CarbonImmutable::parse('2026-11-01'),
            CarbonImmutable::parse('2026-11-01'),
        );

        $this->assertSame(['page_rows' => 1, 'query_rows' => 1, 'truncated_sets' => 0], $result);

        $this->assertDatabaseHas('seo_performance_snapshots', [
            'date' => '2026-11-01',
            'dimension' => 'page',
            'page_url' => 'https://sheykhan.test/courses/math',
            'clicks' => 17,
            'impressions' => 340,
        ]);

        $this->assertDatabaseHas('seo_performance_snapshots', [
            'date' => '2026-11-01',
            'dimension' => 'query',
            'page_url' => 'https://sheykhan.test/courses/math',
            'query' => 'کلاس آنلاین ریاضی',
            'clicks' => 7,
            'impressions' => 120,
        ]);

        Http::assertSentCount(3);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'refresh_token');
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/searchAnalytics/query')
            && $request->hasHeader('Authorization', 'Bearer temporary-access-token')
            && ($request['dimensions'] ?? []) === ['page', 'date']);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/searchAnalytics/query')
            && ($request['dimensions'] ?? []) === ['page', 'query', 'date']);
    }

    public function test_sync_refuses_to_run_without_private_oauth_configuration(): void
    {
        config([
            'services.google_search_console.client_id' => null,
            'services.google_search_console.client_secret' => null,
            'services.google_search_console.refresh_token' => null,
        ]);

        try {
            app(GoogleSearchConsoleSyncService::class)->sync(
                CarbonImmutable::parse('2026-11-01'),
                CarbonImmutable::parse('2026-11-01'),
            );

            $this->fail('Expected the service to reject incomplete OAuth configuration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('not configured', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_sync_upserts_the_same_daily_key_without_creating_duplicate_snapshots(): void
    {
        Http::fakeSequence()
            ->push(['access_token' => 'first-access-token'], 200)
            ->push(['rows' => [[
                'keys' => ['https://sheykhan.test/courses/math', '2026-11-01'],
                'clicks' => 5,
                'impressions' => 100,
                'ctr' => 0.05,
                'position' => 9,
            ]]], 200)
            ->push(['rows' => []], 200)
            ->push(['access_token' => 'second-access-token'], 200)
            ->push(['rows' => [[
                'keys' => ['https://sheykhan.test/courses/math', '2026-11-01'],
                'clicks' => 8,
                'impressions' => 120,
                'ctr' => 0.066667,
                'position' => 7,
            ]]], 200)
            ->push(['rows' => []], 200);

        $service = app(GoogleSearchConsoleSyncService::class);
        $start = CarbonImmutable::parse('2026-11-01');

        $service->sync($start, $start);
        $service->sync($start, $start);

        $this->assertDatabaseCount('seo_performance_snapshots', 1);
        $this->assertDatabaseHas('seo_performance_snapshots', [
            'date' => '2026-11-01',
            'dimension' => 'page',
            'clicks' => 8,
            'impressions' => 120,
        ]);
    }
}
