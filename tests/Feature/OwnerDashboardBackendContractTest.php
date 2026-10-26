<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\FinancialTransaction;
use App\Models\Permission;
use App\Models\ParentMessage;
use App\Models\Role;
use App\Models\User;
use App\Services\OwnerCacheService;
use App\Services\OwnerDashboardService;
use App\Services\OwnerClassroomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $classroomRosterService = app(OwnerClassroomService::class);
        $academy = $classroom->academy;
        $beforeClassroom = $classroomRosterService->index($owner, $academy)->getCollection()->firstWhere('id', $classroom->id);
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
        $afterClassroom = $classroomRosterService->index($owner, $academy)->getCollection()->firstWhere('id', $classroom->id);
        $afterCourse = $afterStudent['recentCourses']->firstWhere('id', $course->id);
        $afterTeacherReport = $afterStudent['teacherReports']->firstWhere('teacher_id', $teacher->id);

        $this->assertSame($beforeStudentCount - 1, (int) $afterStudent['metrics']['students']);
        $this->assertSame($beforeClassroomRoster - 1, (int) $afterClassroom->active_students_count);
        $this->assertLessThan($beforeCourseRoster, (int) $afterCourse->active_students_count);
        $this->assertSame($beforeTeacherRoster - 1, (int) $afterTeacherReport->student_count);

        $teacher->forceFill(['status' => 'inactive'])->save();

        $afterTeacher = $service->build($owner);
        $this->assertSame($beforeTeacherCount - 1, (int) $afterTeacher['metrics']['teachers']);
        $afterTeacherClassroom = $classroomRosterService->index($owner, $academy)
            ->getCollection()
            ->firstWhere('id', $classroom->id);
        $this->assertNotNull($afterTeacherClassroom);
        $this->assertFalse($afterTeacherClassroom->teachers->contains('id', $teacher->id));
    }


    public function test_owner_dashboard_nets_current_period_toman_transactions_without_mixing_rials(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('id')->firstOrFail();
        $academyIds = $owner->ownedAcademies()->where('status', 'active')->pluck('academies.id');
        $periodStart = now()->copy()->startOfMonth();
        $periodEnd = now();

        $basePayments = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
            ->where('type', 'enrollment_payment')
            ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [
                $periodStart->toDateTimeString(),
                $periodEnd->toDateTimeString(),
            ])
            ->sum('amount');
        $baseRefunds = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
            ->where('type', 'refund')
            ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [
                $periodStart->toDateTimeString(),
                $periodEnd->toDateTimeString(),
            ])
            ->sum('amount');

        foreach ([
            ['type' => 'enrollment_payment', 'amount' => 1250000, 'currency' => 'IRT', 'reference' => 'dash-pay-' . Str::random(8), 'occurred_at' => now()],
            ['type' => 'refund', 'amount' => 250000, 'currency' => 'IRT', 'reference' => 'dash-refund-' . Str::random(8), 'occurred_at' => now()],
            ['type' => 'enrollment_payment', 'amount' => 9900000, 'currency' => 'IRR', 'reference' => 'dash-rial-' . Str::random(8), 'occurred_at' => now()],
            ['type' => 'enrollment_payment', 'amount' => 10000000, 'currency' => 'IRT', 'reference' => 'dash-old-' . Str::random(8), 'occurred_at' => now()->subMonths(2)],
        ] as $transaction) {
            FinancialTransaction::query()->create([
                'academy_id' => $academy->id,
                'user_id' => $owner->id,
                'type' => $transaction['type'],
                'status' => 'completed',
                'amount' => $transaction['amount'],
                'currency' => $transaction['currency'],
                'reference' => $transaction['reference'],
                'occurred_at' => $transaction['occurred_at'],
            ]);
        }

        $dashboard = app(OwnerDashboardService::class)->build($owner, 'month');

        $this->assertSame(
            ($basePayments - $baseRefunds) + 1000000.0,
            (float) $dashboard['metrics']['sales'],
            'The dashboard should total current-month IRT/toman payments minus refunds, excluding rial-denominated and out-of-period transactions.'
        );
    }

    public function test_owner_dashboard_uses_one_sidebar_navigation_and_permission_scoped_kpis(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('name')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $ownerRole->permissions()->sync(
            Permission::query()->whereIn('name', [
                'dashboard.view', 'academy.view', 'classrooms.view', 'courses.view', 'attendance.view',
                'finance.view', 'reports.view', 'assignments.view', 'live_classes.view',
                'parents.view', 'content.manage', 'products.view', 'orders.view',
                'legal.manage', 'exams.view', 'teacher_payments.view',
            ])->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('owner.classrooms.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.courses.index') . '"', false)
            ->assertSee('href="' . route('owner.attendance.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.resources.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.products.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.orders.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.legal-documents.index') . '"', false)
            ->assertSee('href="' . route('owner.reports.index') . '"', false)
            ->assertSee('href="' . route('owner.finance.index') . '"', false)
            ->assertDontSee('href="' . route('owner.website.index') . '"', false)
            ->assertDontSee('href="' . route('owner.content.index') . '"', false)
            ->assertDontSee('href="' . route('owner.blog.index') . '"', false)
            ->assertSee('href="' . route('owner.communications.index', $academy) . '"', false)
            ->assertSee('aria-label="کلاس‌های فعال"', false)
            ->assertSee('class="card kpi c3" style="--c:#ff1f6b"', false)
            ->assertSee('id="capacity-title"', false)
            ->assertSee('id="course-publish-title"', false)
            ->assertDontSee('owner-classroom-item', false)
            ->assertDontSee('owner-quick-actions', false)
            ->assertDontSee('owner-footer-actions', false)
            ->assertDontSee('<a class="card kpi', false);
    }


    public function test_multi_academy_dashboard_keeps_kpis_non_navigational_and_scoped_routes_in_sidebar(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آموزشگاه دوم',
            'slug' => 'owner-second-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertDontSee('id="owner-academies"', false)
            ->assertDontSee('href="#owner-academies"', false)
            ->assertDontSee('<a class="owner-stat-card', false)
            ->assertSee('class="card kpi c3" style="--c:#ff1f6b"', false)
            ->assertSee('آموزشگاه دوم')
            ->assertSee('id="capacity-title"', false);
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
            ->assertSee('id="capacity-title"', false)
            ->assertSee('href="' . route('owner.classrooms.index', $academy) . '"', false)
            ->assertDontSee('دانش‌آموزان فعال')
            ->assertDontSee('مدرس‌های فعال')
            ->assertDontSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertDontSee('href="' . route('owner.academy.edit', $academy) . '"', false)
            ->assertDontSee('href="' . route('owner.academy.banners.edit', $academy) . '"', false)
            ->assertDontSee('دوره‌های منتشرشده')
            ->assertDontSee('حضور امروز')
            ->assertDontSee('خالص تراکنش‌های تکمیل‌شده')
            ->assertDontSee('گزارش‌های آموزشی')
            ->assertDontSee('href="' . route('owner.finance.index') . '"', false)
            ->assertDontSee('href="' . route('owner.website.index') . '"', false)
            ->assertDontSee('href="' . route('owner.content.index') . '"', false)
            ->assertDontSee('href="' . route('owner.blog.index') . '"', false);

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

        $parent = User::query()->where('email', 'parent.armin@sheykhan.test')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $message = ParentMessage::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'category' => 'question',
            'subject' => 'پیام فقط‌خواندنی تست',
            'body' => 'این پیام برای بررسی دسترسی مشاهده ثبت شده است.',
            'status' => 'open',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('href="' . route('owner.people.index', $academy) . '"', false)
            ->assertSee('href="' . route('owner.communications.index', $academy) . '"', false);

        $this->get(route('owner.communications.index', $academy))
            ->assertOk()
            ->assertSee('این حساب دسترسی مشاهده دارد')
            ->assertDontSee('action="' . route('owner.communications.store', $academy) . '"', false);

        $this->get(route('owner.messages.index', $academy))
            ->assertOk()
            ->assertSee('پیام فقط‌خواندنی تست')
            ->assertSee('حالت فقط‌خواندنی')
            ->assertDontSee('action="' . route('owner.messages.reply', [$academy, $message->id]) . '"', false);

        $this->patch(route('owner.messages.reply', [$academy, $message->id]), [
            'status' => 'responded',
            'response' => 'پاسخ غیرمجاز',
        ])->assertForbidden();
    }

    public function test_owner_capacity_alerts_ignore_inactive_and_soft_deleted_student_memberships(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('name')->firstOrFail();
        $course = $academy->courses()->firstOrFail();
        $service = app(OwnerDashboardService::class);
        $beforeAlerts = (int) $service->build($owner)['metrics']['capacityAlerts'];

        $classroom = $academy->classrooms()->create([
            'course_id' => $course->id,
            'title' => 'کلاس تست ظرفیت کاربران حذف‌شده',
            'code' => 'CAP-' . Str::upper(Str::random(8)),
            'capacity' => 1,
            'status' => 'active',
        ]);

        $student = User::factory()->create(['status' => 'active']);
        $classroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        // Soft-deleting the account must immediately stop it counting toward live capacity.
        $student->delete();

        $this->assertSame(
            $beforeAlerts,
            (int) $service->build($owner)['metrics']['capacityAlerts'],
            'Soft-deleted accounts must not trigger false classroom capacity alerts.'
        );
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
