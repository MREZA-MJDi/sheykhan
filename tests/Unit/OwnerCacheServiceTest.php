<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\OwnerCacheService;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class OwnerCacheServiceTest extends TestCase
{
    public function test_dashboard_data_is_returned_when_the_cache_store_is_unavailable(): void
    {
        $owner = new User();
        $owner->setAttribute('id', 123);

        Cache::shouldReceive('get')->once()->with('owner:dashboard:123')
            ->andThrow(new RuntimeException('cache backend unavailable'));
        Cache::shouldReceive('put')->once()
            ->andThrow(new RuntimeException('cache backend unavailable'));

        $expected = ['metrics' => ['students' => 7]];
        $actual = app(OwnerCacheService::class)->rememberDashboard(
            $owner,
            static fn (): array => $expected,
        );

        $this->assertSame($expected, $actual);
    }

    public function test_cache_invalidation_failure_does_not_throw_after_a_successful_write(): void
    {
        $owner = new User();
        $owner->setAttribute('id', 123);

        Cache::shouldReceive('forget')->twice()
            ->andThrow(new RuntimeException('cache backend unavailable'));

        app(OwnerCacheService::class)->forget($owner);

        $this->assertTrue(true);
    }

    public function test_database_or_resolver_failures_are_not_hidden_as_cache_misses(): void
    {
        $owner = new User();
        $owner->setAttribute('id', 123);

        Cache::shouldReceive('get')->once()->with('owner:dashboard:123')->andReturn(null);
        Cache::shouldNotReceive('put');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('source query failed');

        app(OwnerCacheService::class)->rememberDashboard(
            $owner,
            static fn (): array => throw new RuntimeException('source query failed'),
        );
    }
}
