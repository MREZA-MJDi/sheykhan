<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Attendance;
use App\Models\CounselingRequest;
use App\Models\Exam;
use App\Models\LiveClass;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_owner_route_requires_authentication_active_account_and_a_permission(): void
    {
        $ownerRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'owner.'));

        $this->assertGreaterThan(0, $ownerRoutes->count(), 'Expected named Owner routes to be registered.');

        foreach ($ownerRoutes as $route) {
            $middleware = strtolower(implode('|', $route->gatherMiddleware()));
            $identity = sprintf('%s [%s]', $route->getName(), implode('|', $route->methods()));

            $this->assertStringContainsString('auth', $middleware, $identity . ' must require authentication.');
            $this->assertStringContainsString('active', $middleware, $identity . ' must require an active account.');
            $this->assertStringContainsString('permission', $middleware, $identity . ' must be permission-gated.');
        }
    }

    public function test_unscoped_owner_workspaces_reject_an_academy_owned_by_another_user(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $permissionNames = [
            'attendance.view', 'attendance.manage',
            'parents.view', 'parents.manage',
            'exams.view', 'exams.manage',
            'live_classes.view', 'live_classes.manage',
            'teacher_payments.view', 'teacher_payments.manage',
        ];
        $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id');
        $this->assertCount(count($permissionNames), $permissionIds, 'The route authorization test depends on the canonical permission seeder.');
        $ownerRole->permissions()->sync($permissionIds);
        $owner->unsetRelation('roles');
        Cache::flush();

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آموزشگاه خارج از محدوده',
            'slug' => 'foreign-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $readRoutes = [
            'owner.attendance.index',
            'owner.communications.index',
            'owner.messages.index',
            'owner.counseling.index',
            'owner.exams.index',
            'owner.recordings.index',
            'owner.teacher-payments.index',
        ];

        foreach ($readRoutes as $routeName) {
            $response = $this->actingAs($owner)
                ->get(route($routeName, $foreignAcademy));

            $this->assertTrue(
                in_array($response->status(), [403, 404], true),
                $routeName . ' must deny access to an academy owned by another user.'
            );
        }
    }

    public function test_owner_cannot_open_or_edit_a_foreign_classroom_through_an_owned_academy_url(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $ownedAcademy = $owner->ownedAcademies()->where('status', 'active')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $ownerRole->permissions()->sync(
            Permission::query()->whereIn('name', [
                'classrooms.view',
                'classrooms.manage',
            ])->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آکادمی کلاس خارجی',
            'slug' => 'foreign-classroom-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);
        $foreignCourse = $foreignAcademy->courses()->create([
            'created_by' => $foreignOwner->id,
            'title' => 'دوره متعلق به آموزشگاه دیگر',
            'slug' => 'foreign-course-' . Str::lower(Str::random(10)),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);
        $foreignClassroom = $foreignAcademy->classrooms()->create([
            'course_id' => $foreignCourse->id,
            'title' => 'کلاس خارج از محدوده',
            'code' => 'FOREIGN-' . Str::upper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.classrooms.show', [$ownedAcademy, $foreignClassroom]))
            ->assertNotFound();

        $this->actingAs($owner)
            ->get(route('owner.classrooms.edit', [$ownedAcademy, $foreignClassroom]))
            ->assertNotFound();
    }

    public function test_owner_cannot_create_messages_or_teacher_payments_for_a_foreign_academy(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $permissionNames = [
            'parents.view', 'parents.manage',
            'teacher_payments.view', 'teacher_payments.manage',
        ];
        $ownerRole->permissions()->sync(
            Permission::query()->whereIn('name', $permissionNames)->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آموزشگاه مقصد غیرمجاز',
            'slug' => 'foreign-write-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.communications.store', $foreignAcademy), [
                'target' => 'all_parents',
                'category' => 'announcement',
                'subject' => 'نباید ثبت شود',
                'body' => 'این اعلان برای آموزشگاه متعلق به مدیر دیگری است.',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('academy_messages', [
            'academy_id' => $foreignAcademy->id,
            'subject' => 'نباید ثبت شود',
        ]);

        $teacher = User::factory()->create(['status' => 'active']);
        $foreignAcademy->users()->attach($teacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('owner.teacher-payments.store', $foreignAcademy), [
                'teacher_id' => $teacher->id,
                'title' => 'نباید پرداخت ثبت شود',
                'amount' => 100000,
                'status' => 'paid',
                'reference' => 'cross-academy-test',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('teacher_payments', [
            'academy_id' => $foreignAcademy->id,
            'title' => 'نباید پرداخت ثبت شود',
        ]);
    }

    public function test_owner_cannot_update_attendance_from_another_academy_using_an_owned_academy_url(): void
    {
        [$owner, $ownedAcademy] = $this->ownerAndAcademyWithPermissions(['attendance.manage']);

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = $this->createForeignAcademy($foreignOwner, 'آموزشگاه حضور خارجی');
        $foreignCourse = $this->createForeignCourse($foreignAcademy, $foreignOwner, 'دوره حضور خارجی');
        $foreignClassroom = $foreignAcademy->classrooms()->create([
            'course_id' => $foreignCourse->id,
            'title' => 'کلاس حضور خارجی',
            'code' => 'ATT-' . Str::upper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $foreignStudent = User::factory()->create(['status' => 'active']);
        $foreignAcademy->users()->attach($foreignStudent->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $attendance = Attendance::query()->create([
            'classroom_id' => $foreignClassroom->id,
            'student_id' => $foreignStudent->id,
            'marked_by' => $foreignOwner->id,
            'attendance_date' => now()->toDateString(),
            'scheduled_minutes' => 60,
            'attended_minutes' => 60,
            'status' => 'present',
            'note' => 'original foreign record',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.attendance.update', [$ownedAcademy, $attendance->id]), [
                'status' => 'absent',
                'scheduled_minutes' => 60,
                'attended_minutes' => 0,
                'note' => 'unauthorized change',
            ])
            ->assertNotFound();

        $attendance->refresh();
        $this->assertSame('present', $attendance->status);
        $this->assertSame('original foreign record', $attendance->note);
        $this->assertSame($foreignOwner->id, $attendance->marked_by);
    }

    public function test_owner_cannot_update_a_counseling_request_from_another_academy(): void
    {
        [$owner, $ownedAcademy] = $this->ownerAndAcademyWithPermissions(['parents.view', 'parents.manage']);

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = $this->createForeignAcademy($foreignOwner, 'آموزشگاه مشاوره خارجی');
        $foreignParent = User::factory()->create(['status' => 'active']);
        $foreignStudent = User::factory()->create(['status' => 'active']);
        $foreignAcademy->users()->attach([
            $foreignParent->id => ['role' => 'parent', 'status' => 'active', 'joined_at' => now()],
            $foreignStudent->id => ['role' => 'student', 'status' => 'active', 'joined_at' => now()],
        ]);
        $counselingRequest = CounselingRequest::query()->create([
            'academy_id' => $foreignAcademy->id,
            'parent_id' => $foreignParent->id,
            'student_id' => $foreignStudent->id,
            'subject' => 'درخواست مشاوره خارج از محدوده',
            'details' => 'درخواست متعلق به آموزشگاه دیگر',
            'preferred_at' => now()->addDay(),
            'status' => 'requested',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.counseling.update', [$ownedAcademy, $counselingRequest->id]), [
                'status' => 'declined',
                'response_notes' => 'unauthorized change',
            ])
            ->assertNotFound();

        $counselingRequest->refresh();
        $this->assertSame('requested', $counselingRequest->status);
        $this->assertNull($counselingRequest->handled_by);
        $this->assertNull($counselingRequest->response_notes);
    }

    public function test_owner_cannot_change_the_status_of_an_exam_from_another_academy(): void
    {
        [$owner, $ownedAcademy] = $this->ownerAndAcademyWithPermissions(['exams.manage']);

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = $this->createForeignAcademy($foreignOwner, 'آموزشگاه آزمون خارجی');
        $foreignCourse = $this->createForeignCourse($foreignAcademy, $foreignOwner, 'دوره آزمون خارجی');
        $foreignExam = $foreignCourse->exams()->create([
            'teacher_id' => $foreignOwner->id,
            'title' => 'آزمون متعلق به آموزشگاه دیگر',
            'description' => 'foreign academy regression fixture',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.exams.status', [$ownedAcademy, $foreignExam->id]), [
                'status' => 'closed',
            ])
            ->assertNotFound();

        $foreignExam->refresh();
        $this->assertSame('draft', $foreignExam->status);
    }

    public function test_owner_cannot_change_recording_publication_for_a_live_class_from_another_academy(): void
    {
        [$owner, $ownedAcademy] = $this->ownerAndAcademyWithPermissions(['live_classes.manage']);

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = $this->createForeignAcademy($foreignOwner, 'آموزشگاه ضبط خارجی');
        $foreignCourse = $this->createForeignCourse($foreignAcademy, $foreignOwner, 'دوره ضبط خارجی');
        $releasedAt = now()->subMinutes(30);
        $foreignLiveClass = LiveClass::query()->create([
            'course_id' => $foreignCourse->id,
            'teacher_id' => $foreignOwner->id,
            'title' => 'جلسه ضبط‌شده متعلق به آموزشگاه دیگر',
            'provider' => 'jitsi',
            'meeting_url' => 'https://meet.jit.si/foreign-academy-regression',
            'scheduled_at' => now()->subHours(2),
            'scheduled_end_at' => now()->subHour(),
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'recording_released_at' => $releasedAt,
            'recording_visibility' => 'enrolled_students',
            'duration_minutes' => 60,
            'status' => 'published',
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.recordings.update', [$ownedAcademy, $foreignLiveClass->id]), [
                'action' => 'unpublish',
            ])
            ->assertNotFound();

        $foreignLiveClass->refresh();
        $this->assertSame('published', $foreignLiveClass->status);
        $this->assertSame('enrolled_students', $foreignLiveClass->recording_visibility);
        $this->assertSame(
            $releasedAt->toDateTimeString(),
            $foreignLiveClass->recording_released_at?->toDateTimeString()
        );
    }

    /**
     * @return array{0: User, 1: Academy}
     */
    private function ownerAndAcademyWithPermissions(array $permissionNames): array
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheikhan.test')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id');
        $this->assertCount(
            count(array_unique($permissionNames)),
            $permissionIds,
            'The cross-academy regression test depends on canonical permissions being seeded.'
        );

        $ownerRole->permissions()->sync($permissionIds);
        $owner->unsetRelation('roles');
        Cache::flush();

        return [$owner, $owner->ownedAcademies()->where('status', 'active')->firstOrFail()];
    }

    private function createForeignAcademy(User $foreignOwner, string $name): Academy
    {
        return Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => $name,
            'slug' => 'foreign-' . Str::lower(Str::random(12)),
            'status' => 'active',
        ]);
    }

    private function createForeignCourse(Academy $academy, User $foreignOwner, string $title)
    {
        return $academy->courses()->create([
            'created_by' => $foreignOwner->id,
            'title' => $title,
            'slug' => 'foreign-course-' . Str::lower(Str::random(12)),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);
    }

}
