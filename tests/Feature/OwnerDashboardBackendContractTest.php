<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\User;
use App\Services\OwnerCacheService;
use App\Services\OwnerDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerDashboardBackendContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_metrics_and_lists_are_scoped_to_owned_active_academies(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آموزشگاه خارج از محدوده',
            'slug' => 'foreign-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        $foreignCourse = Course::query()->create([
            'academy_id' => $foreignAcademy->id,
            'created_by' => $foreignOwner->id,
            'title' => 'دورهٔ خارج از محدوده',
            'slug' => 'foreign-course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $dashboard = app(OwnerDashboardService::class)->build($owner);
        $ownedAcademyIds = $owner->ownedAcademies()
            ->where('status', 'active')
            ->pluck('academies.id');

        $this->assertSame(
            Course::query()->whereIn('academy_id', $ownedAcademyIds)->count(),
            $dashboard['metrics']['courses'],
        );
        $this->assertFalse($dashboard['academies']->contains('id', $foreignAcademy->id));
        $this->assertFalse($dashboard['recentCourses']->contains(
            fn (Course $course): bool => (int) $course->id === (int) $foreignCourse->id
        ));
    }

    public function test_owner_dashboard_cache_is_per_owner_and_can_be_invalidated_after_a_write(): void
    {
        Cache::flush();

        $firstOwner = User::factory()->create();
        $secondOwner = User::factory()->create();
        $cache = app(OwnerCacheService::class);
        $firstCalls = 0;
        $secondCalls = 0;

        $first = $cache->rememberDashboard($firstOwner, function () use (&$firstCalls): array {
            return ['value' => ++$firstCalls];
        });
        $cached = $cache->rememberDashboard($firstOwner, function () use (&$firstCalls): array {
            return ['value' => ++$firstCalls];
        });
        $otherOwner = $cache->rememberDashboard($secondOwner, function () use (&$secondCalls): array {
            return ['value' => ++$secondCalls];
        });

        $this->assertSame(['value' => 1], $first);
        $this->assertSame(['value' => 1], $cached);
        $this->assertSame(['value' => 1], $otherOwner);
        $this->assertSame(1, $firstCalls);
        $this->assertSame(1, $secondCalls);

        $cache->forget($firstOwner);
        $afterInvalidation = $cache->rememberDashboard($firstOwner, function () use (&$firstCalls): array {
            return ['value' => ++$firstCalls];
        });

        $this->assertSame(['value' => 2], $afterInvalidation);
        $this->assertSame(2, $firstCalls);
        $this->assertSame(1, $secondCalls);
    }
}
