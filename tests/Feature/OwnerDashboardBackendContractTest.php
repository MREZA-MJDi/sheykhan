<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
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

    public function test_owner_dashboard_rosters_exclude_inactive_user_accounts(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $service = app(OwnerDashboardService::class);
        $before = $service->build($owner);
        $beforeClassroom = $before['classrooms']->firstWhere('id', $classroom->id);
        $beforeCourse = $before['recentCourses']->firstWhere('id', $course->id);
        $beforeTeacherReport = $before['teacherReports']->firstWhere('teacher_id', $teacher->id);

        $this->assertGreaterThan(0, $before['metrics']['teachers']);
        $this->assertGreaterThan(0, $before['metrics']['students']);
        $this->assertNotNull($beforeClassroom);
        $this->assertNotNull($beforeCourse);
        $this->assertNotNull($beforeTeacherReport);
        $this->assertGreaterThan(0, (int) $beforeClassroom->active_students_count);
        $this->assertGreaterThan(0, (int) $beforeCourse->active_students_count);
        $this->assertGreaterThan(0, (int) $beforeTeacherReport->student_count);

        $beforeStudentCount = (int) $before['metrics']['students'];
        $beforeClassroomRoster = (int) $beforeClassroom->active_students_count;
        $beforeCourseRoster = (int) $beforeCourse->active_students_count;
        $beforeTeacherRoster = (int) $beforeTeacherReport->student_count;
        $beforeTeacherCount = (int) $before['metrics']['teachers'];

        $student->forceFill(['status' => 'inactive'])->save();

        $afterStudent = $service->build($owner);
        $afterClassroom = $afterStudent['classrooms']->firstWhere('id', $classroom->id);
        $afterCourse = $afterStudent['recentCourses']->firstWhere('id', $course->id);
        $afterTeacherReport = $afterStudent['teacherReports']->firstWhere('teacher_id', $teacher->id);

        $this->assertSame($beforeStudentCount - 1, (int) $afterStudent['metrics']['students']);
        $this->assertSame($beforeClassroomRoster - 1, (int) $afterClassroom->active_students_count);
        $this->assertLessThan($beforeCourseRoster, (int) $afterCourse->active_students_count);
        $this->assertSame($beforeTeacherRoster - 1, (int) $afterTeacherReport->student_count);

        $teacher->forceFill(['status' => 'inactive'])->save();

        $afterTeacher = $service->build($owner);
        $this->assertSame($beforeTeacherCount - 1, (int) $afterTeacher['metrics']['teachers']);
        $this->assertFalse($afterTeacher['classrooms']->contains(
            fn ($item): bool => $item->teachers->contains('id', $teacher->id)
        ));
    }


    public function test_owner_dashboard_kpis_and_shortcuts_link_to_registered_permission_scoped_workspaces(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('name')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('owner.classrooms.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.courses.index') . '"', false)
            ->assertSee('href="' . route('owner.attendance.index', $academy) . '"', false)
            ->assertSee('aria-label="کلاس‌های فعال"', false)
            ->assertSee('owner-quick-actions', false);
    }

    public function test_owner_dashboard_hides_ungranted_kpis_and_shortcuts(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('name')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $ownerRole->permissions()->sync(
            Permission::query()
                ->whereIn('name', ['dashboard.view', 'classrooms.view'])
                ->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('کلاس‌های فعال')
            ->assertSee('href="' . route('owner.classrooms.index', $academy) . '"', false)
            ->assertDontSee('ثبت‌نام و مدیریت اعضا')
            ->assertDontSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertDontSee('دوره‌های منتشرشده')
            ->assertDontSee('حضور امروز')
            ->assertDontSee('درآمد خالص ثبت‌شده')
            ->assertDontSee('گزارش‌های آموزشی');

        $this->get(route('owner.people.index', $academy))->assertForbidden();
    }


    public function test_owner_read_only_parent_message_permission_keeps_get_navigation_available(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('name')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $ownerRole->permissions()->sync(
            Permission::query()
                ->whereIn('name', ['dashboard.view', 'academy.view', 'parents.view'])
                ->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.communications.index', $academy) . '"', false);

        $this->get(route('owner.communications.index', $academy))->assertOk();
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
