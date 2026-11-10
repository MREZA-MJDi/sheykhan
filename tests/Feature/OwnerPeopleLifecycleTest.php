<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Services\ParentOnboardingService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OwnerPeopleLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_archive_and_restore_teacher_without_deleting_history(): void
    {
        [$owner, $academy, $teacher] = $this->makeWorkspace();

        // The account has two distinct academy roles. Fixture creation uses the
        // unscoped relation so the student role is explicit in the pivot row.
        $academy->users()->attach($teacher->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('عضو فعال')
            ->assertSee($teacher->name);

        $this->actingAs($owner)
            ->patch(route('owner.people.archive-teacher', [$academy, $teacher]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'teacher',
            'status' => 'archived',
        ]);
        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'student',
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('مدرس‌های آرشیوشده')
            ->assertSee('آرشیوشده');

        $this->actingAs($owner)
            ->patch(route('owner.people.restore-teacher', [$academy, $teacher]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'teacher',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    public function test_public_visibility_is_independent_from_membership_archive(): void
    {
        [$owner, $academy, $teacher] = $this->makeWorkspace();

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertSee($teacher->name);

        $this->actingAs($owner)
            ->patch(route('owner.people.archive-teacher', [$academy, $teacher]))
            ->assertSessionHas('success');

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertSee($teacher->name);

        $this->actingAs($owner)
            ->patch(route('owner.people.teacher-visibility', [$academy, $teacher]), [
                'is_public' => false,
            ])
            ->assertSessionHas('success');

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertDontSee($teacher->name);

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $teacher->id,
            'is_public' => false,
        ]);
    }

    public function test_archived_teacher_cannot_use_teacher_portal_or_course_management(): void
    {
        [$owner, $academy, $teacher, $course] = $this->makeWorkspace(true);

        $teacher->load('roles');

        $this->actingAs($owner)
            ->patch(route('owner.people.archive-teacher', [$academy, $teacher]))
            ->assertSessionHas('success');

        $this->actingAs($teacher)
            ->get(route('teacher.dashboard'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.edit', $course))
            ->assertForbidden();
    }

    public function test_people_workspace_stays_available_when_onboarding_schema_is_missing(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        // Simulate a production deployment where the optional legacy-onboarding
        // migration has not been applied yet, without dropping tables in CI.
        Schema::shouldReceive('hasTable')
            ->andReturnUsing(fn (string $table): bool => $table !== 'student_onboardings');
        Schema::shouldReceive('hasColumn')->andReturn(true);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('بخشی از امکانات ورود دانش‌آموز قدیمی موقتاً غیرفعال است')
            ->assertSee('ساخت حساب مدرس')
            ->assertSee('سوابق ورود دانش‌آموز')
            ->assertSee('موقتاً غیرفعال')
            ->assertSee('owner-create-parent-dialog', false)
            ->assertSee('owner-create-teacher-dialog', false)
            ->assertSee('owner-assign-class-dialog', false)
            ->assertSee('owner-enroll-student-dialog', false)
            ->assertSee('data-owner-modal-open', false);
    }

    public function test_people_workspace_stays_available_when_academic_grade_schema_is_missing(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        // Simulate a partial production migration: the onboarding table and its
        // columns exist, but the academic-grade table used by requestedGrade
        // has not been deployed. The page must not query that missing relation.
        Schema::shouldReceive('hasTable')
            ->andReturnUsing(fn (string $table): bool => $table !== 'academic_grades');
        Schema::shouldReceive('hasColumn')->andReturn(true);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('فهرست اعضای آموزشگاه در دسترس است')
            ->assertSee('ساخت حساب مدرس')
            ->assertSee('ساخت حساب والد و اتصال به دانش‌آموز')
            ->assertSee('تاریخچه فعلاً قابل نمایش نیست');
    }

    public function test_people_workspace_loads_price_fields_for_paid_course_options(): void
    {
        [$owner, $academy, $teacher, $course] = $this->makeWorkspace(true);
        $course->update([
            'access_type' => 'paid',
            'price' => 250000,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee(\App\Support\PersianUi::money($course->fresh()->price))
            ->assertDontSee('قیمت تعیین نشده')
            ->assertSee('ساخت حساب مدرس')
            ->assertSee('ساخت حساب والد و اتصال به دانش‌آموز');
    }

    public function test_student_roster_and_enrollment_search_are_server_paginated(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        $studentRole = Role::create([
            'name' => 'دانش‌آموز',
            'slug' => 'student',
            'description' => 'Student',
        ]);

        for ($i = 1; $i <= 30; $i++) {
            $student = User::factory()->create([
                'name' => sprintf('دانش‌آموز %02d', $i),
                'email' => sprintf('student-%02d@test.local', $i),
                'mobile' => sprintf('0913%07d', $i),
            ]);
            $student->roles()->attach($studentRole->id);
            $academy->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $response = $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertViewHas('students', fn ($students) => $students->total() === 30 && $students->count() === 25)
            ->assertSee('data-student-picker')
            ->assertSee('owner-parent-student-search', false)
            ->assertSee('data-student-initial-browse="true"', false)
            ->assertDontSee('<select name="student_id"', false)
            ->assertSee('آرشیو عضویت')
            ->assertDontSee('student-30@test.local');

        $this->assertStringContainsString('students_page=', $response->getContent());

        $searchUrl = route('owner.people.student-options', $academy);
        $this->actingAs($owner)
            ->get($searchUrl . '?q=09130000030')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissingPath('data.0.email');

        $this->actingAs($owner)
            ->get($searchUrl . '?q=دانش‌آموز')
            ->assertOk()
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(20, 'data');

        $this->actingAs($owner)
            ->get($searchUrl . '?q=دانش‌آموز&page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(10, 'data')
            ->assertJsonMissingPath('data.9.email');

        // The parent form can browse a large roster without rendering every student into HTML.
        $this->actingAs($owner)
            ->getJson($searchUrl . '?browse=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.next_page', 2)
            ->assertJsonCount(20, 'data');

        $this->actingAs($owner)
            ->getJson($searchUrl . '?browse=1&page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(10, 'data');
    }

    public function test_parent_onboarding_rejects_a_student_outside_the_selected_academy_as_a_validation_error(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        try {
            app(ParentOnboardingService::class)->createOrLink($academy, [
                'student_id' => 999999,
                'name' => 'والد آزمایشی',
                'mobile' => '09120000000',
                'relation' => 'مادر',
            ]);
            $this->fail('An unknown student must not be linked to a parent.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('student_id', $exception->errors());
        }
    }

    public function test_assigning_an_existing_course_teacher_does_not_demote_the_primary_teacher(): void
    {
        [$owner, $academy, $teacher, $course] = $this->makeWorkspace(true);

        $this->actingAs($owner)
            ->post(route('owner.people.assign-teacher', $academy), [
                'teacher_id' => $teacher->id,
                'course_id' => $course->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'is_primary' => true,
        ]);
    }

    public function test_owner_created_teacher_password_is_hashed_and_requires_change_on_first_login(): void
    {
        [$owner, $academy] = $this->makeWorkspace();
        $mobile = sprintf('0912%07d', random_int(0, 9999999));

        $this->actingAs($owner)
            ->post(route('owner.people.store-teacher', $academy), [
                'first_name' => 'مدرس',
                'last_name' => 'تازه',
                'mobile' => $mobile,
                'specialization' => 'ریاضی',
                'experience_years' => 4,
            ])
            ->assertRedirect()
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        $teacher = User::query()->where('mobile', $mobile)->firstOrFail();
        $plainPassword = session('temporary_teacher_password');
        $this->assertIsString($plainPassword);
        $this->assertTrue(Hash::check($plainPassword, $teacher->password));
        $this->assertTrue($teacher->must_change_password);
        $this->assertTrue($teacher->hasRole('teacher'));

        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'teacher',
            'status' => 'active',
        ]);
    }

    public function test_people_directory_api_searches_and_uses_cursor_pagination_without_cross_academy_rows(): void
    {
        [$owner, $academy] = $this->makeWorkspace();
        $teacherRole = Role::query()->where('slug', 'teacher')->firstOrFail();

        for ($i = 1; $i <= 26; $i++) {
            $teacher = User::factory()->create([
                'name' => sprintf('Directory Teacher %02d', $i),
                'email' => sprintf('directory-teacher-%02d-%s@test.local', $i, Str::lower(Str::random(6))),
                'mobile' => sprintf('0912%07d', $i),
                'status' => 'active',
            ]);
            $teacher->roles()->attach($teacherRole->id);
            $academy->users()->attach($teacher->id, [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now()->subMinutes($i),
            ]);
        }

        $foreignOwner = User::factory()->create([
            'email' => 'directory-foreign-owner-' . Str::lower(Str::random(6)) . '@test.local',
        ]);
        $foreignAcademy = Academy::create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آکادمی خارج از محدوده',
            'slug' => 'directory-foreign-' . Str::lower(Str::random(6)),
            'status' => 'active',
        ]);
        $foreignTeacher = User::factory()->create([
            'name' => 'Directory Teacher Foreign',
            'email' => 'directory-foreign-teacher-' . Str::lower(Str::random(6)) . '@test.local',
            'status' => 'active',
        ]);
        $foreignTeacher->roles()->attach($teacherRole->id);
        $foreignAcademy->users()->attach($foreignTeacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $directoryUrl = route('owner.people.data', [
            'academy' => $academy,
            'role' => 'active-teachers',
        ]);

        $firstPage = $this->actingAs($owner)
            ->getJson($directoryUrl . '?' . http_build_query([
                'q' => 'Directory Teacher',
                'per_page' => 25,
                'sort' => 'name',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertJsonPath('directory.role', 'active-teachers')
            ->assertJsonPath('directory.pagination_mode', 'cursor')
            ->assertJsonPath('data.0.account_status', 'active')
            ->assertJsonPath('meta.per_page', 25)
            ->assertJsonCount(25, 'data')
            ->assertJsonMissing(['name' => 'Directory Teacher Foreign']);

        $nextCursor = $firstPage->json('meta.next_cursor');
        $this->assertIsString($nextCursor);
        $this->assertNotSame('', $nextCursor);

        $secondPage = $this->actingAs($owner)
            ->getJson($directoryUrl . '?' . http_build_query([
                'q' => 'Directory Teacher',
                'per_page' => 25,
                'sort' => 'name',
                'direction' => 'asc',
                'cursor' => $nextCursor,
            ]))
            ->assertOk()
            ->assertJsonPath('directory.pagination_mode', 'cursor')
            ->assertJsonCount(1, 'data');

        $this->assertStringContainsString('Directory Teacher', $secondPage->json('data.0.name'));
        $this->assertSame('active', $secondPage->json('data.0.membership.status'));

        $this->actingAs($owner)
            ->getJson($directoryUrl . '?' . http_build_query([
                'q' => 'Directory Teacher 03',
                'sort' => 'mobile',
                'per_page' => 7,
            ]))
            ->assertOk()
            ->assertJsonPath('directory.pagination_mode', 'page')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 7)
            ->assertJsonCount(1, 'data');

        $this->actingAs($owner)
            ->getJson(route('owner.people.data', [
                'academy' => $foreignAcademy,
                'role' => 'students',
            ]))
            ->assertForbidden();
    }

    public function test_owner_people_roster_renders_member_preview_for_the_browser(): void
    {
        [$owner, $academy, $teacher] = $this->makeWorkspace();
        $this->actingAs($owner)->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('data-owner-person-preview-dialog', false)
            ->assertSee('data-owner-person-preview', false)
            ->assertSee('data-person-role="active-teachers"', false)
            ->assertSee('data-person-account-status="active"', false)
            ->assertSee('data-person-membership-status="active"', false)
            ->assertSee('پیش‌نمایش')
            ->assertSee($teacher->name);
    }

    public function test_active_member_rosters_exclude_inactive_user_accounts(): void
    {
        [$owner, $academy] = $this->makeWorkspace();
        $studentRole = Role::create([
            'name' => 'دانش‌آموز',
            'slug' => 'student',
            'description' => 'Student',
        ]);
        $inactiveStudent = User::factory()->create([
            'name' => 'دانش‌آموز حساب غیرفعال',
            'email' => 'inactive-student-' . Str::random(8) . '@test.local',
            'status' => 'inactive',
        ]);
        $inactiveStudent->roles()->attach($studentRole->id);
        $academy->users()->attach($inactiveStudent->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertDontSee('دانش‌آموز حساب غیرفعال');
    }

    public function test_owner_can_assign_a_single_teacher_to_a_class_from_the_people_page(): void
    {
        [$owner, $academy, $teacher, $course] = $this->makeWorkspace(true);

        $classroom = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس گردش کار اعضا',
            'code' => 'PEOPLE-101',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.people.assign-class-teacher', $academy), [
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classroom_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
        ]);
        $this->assertDatabaseHas('course_teacher', [
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'is_primary' => true,
        ]);
        $this->assertSame(1, $classroom->teachers()->count());
    }

    public function test_owner_people_page_uses_separate_member_sheets_and_floating_crud_dialogs(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('id="owner-members-students-panel"', false)
            ->assertSee('id="owner-members-parents-panel"', false)
            ->assertSee('id="owner-members-active-teachers-panel"', false)
            ->assertSee('id="owner-members-archived-teachers-panel"', false)
            ->assertSee('id="owner-parent-create-modal"', false)
            ->assertSee('id="owner-teacher-create-modal"', false)
            ->assertSee('id="owner-enrollment-modal"', false)
            ->assertSee('data-directory-pagination', false);
    }

    public function test_course_and_classroom_create_forms_can_render_as_same_page_modal_fragments(): void
    {
        [$owner, $academy] = $this->makeWorkspace(true);

        $this->actingAs($owner)
            ->get(route('owner.courses.create', ['modal' => 1]))
            ->assertOk()
            ->assertDontSee('<!doctype html>', false)
            ->assertSee('course-form-shell', false)
            ->assertSee('name="title"', false);

        $classroomsManage = Permission::create([
            'name' => 'classrooms.manage',
            'label' => 'Manage classrooms',
            'group' => 'classrooms',
        ]);
        Role::query()
            ->where('slug', 'academy-owner')
            ->firstOrFail()
            ->permissions()
            ->attach($classroomsManage->id);

        $this->actingAs($owner)
            ->get(route('owner.classrooms.create', ['academy' => $academy, 'modal' => 1]))
            ->assertOk()
            ->assertDontSee('<!doctype html>', false)
            ->assertSee('data-owner-classroom-form', false)
            ->assertSee('data-classroom-course', false)
            ->assertSee('data-classroom-teachers', false);
    }

    public function test_owner_can_update_weekly_schedule_without_creating_an_overlap(): void
    {
        [$owner, $academy, , $course] = $this->makeWorkspace(true);

        $liveClassesManage = Permission::create([
            'name' => 'live_classes.manage',
            'label' => 'Manage live classes',
            'group' => 'live_classes',
        ]);
        Role::query()
            ->where('slug', 'academy-owner')
            ->firstOrFail()
            ->permissions()
            ->attach($liveClassesManage->id);

        $classroom = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس تست برنامه آنلاین',
            'code' => 'SCHEDULE-TEST',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $schedule = $classroom->schedules()->create([
            'weekday' => 6,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'room' => 'اتاق یک',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/Sheykhan-Test-Room',
        ]);

        $service = app(\App\Services\OwnerScheduleService::class);
        $links = app(\App\Services\MeetingLinkService::class);

        $updated = $service->update($owner, $academy, $classroom, $schedule, [
            'weekday' => 6,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room' => 'اتاق دو',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/Sheykhan-Updated-Room',
        ], $links);

        $this->assertSame('10:00:00', $updated->start_time);
        $this->assertSame('11:00:00', $updated->end_time);
        $this->assertSame('اتاق دو', $updated->room);
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'classroom_id' => $classroom->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $classroom->schedules()->create([
            'weekday' => 6,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/Sheykhan-Conflict-Room',
        ]);

        try {
            $service->update($owner, $academy, $classroom, $updated, [
                'weekday' => 6,
                'start_time' => '12:30',
                'end_time' => '13:30',
                'room' => null,
                'provider' => 'Jitsi',
                'meeting_url' => 'https://meet.jit.si/Sheykhan-Overlap-Room',
            ], $links);
            $this->fail('An overlapping weekly schedule should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('start_time', $exception->errors());
        }
    }

    public function test_resource_and_product_create_forms_are_floating_dialogs_on_their_lists(): void
    {
        [$owner, $academy] = $this->makeWorkspace();

        $resourcesView = Permission::create([
            'name' => 'resources.view',
            'label' => 'View resources',
            'group' => 'resources',
        ]);
        $resourcesManage = Permission::create([
            'name' => 'resources.manage',
            'label' => 'Manage resources',
            'group' => 'resources',
        ]);
        $productsView = Permission::create([
            'name' => 'products.view',
            'label' => 'View products',
            'group' => 'products',
        ]);
        $productsManage = Permission::create([
            'name' => 'products.manage',
            'label' => 'Manage products',
            'group' => 'products',
        ]);

        Role::query()
            ->where('slug', 'academy-owner')
            ->firstOrFail()
            ->permissions()
            ->attach([
                $resourcesView->id,
                $resourcesManage->id,
                $productsView->id,
                $productsManage->id,
            ]);

        $this->actingAs($owner)
            ->get(route('owner.resources.index', $academy))
            ->assertOk()
            ->assertSee('id="owner-resource-create-modal"', false)
            ->assertSee('data-owner-modal-open="owner-resource-create-modal"', false)
            ->assertSee('name="form_modal" value="resource"', false);

        $this->actingAs($owner)
            ->get(route('owner.products.index', $academy))
            ->assertOk()
            ->assertSee('id="owner-product-create-modal"', false)
            ->assertSee('data-owner-modal-open="owner-product-create-modal"', false)
            ->assertSee('name="form_modal" value="product"', false);
    }

    private function makeWorkspace(bool $withCourse = false): array
    {
        $owner = User::factory()->create(['email' => 'owner-' . Str::random(6) . '@test.local']);
        $teacher = User::factory()->create(['email' => 'teacher-' . Str::random(6) . '@test.local']);

        $ownerRole = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);
        $teacherRole = Role::create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);

        $academyView = Permission::create([
            'name' => 'academy.view',
            'label' => 'View academy',
            'group' => 'academy',
        ]);

        $teacherManage = Permission::create([
            'name' => 'teachers.manage',
            'label' => 'Manage teachers',
            'group' => 'teachers',
        ]);
        $coursesManage = Permission::create([
            'name' => 'courses.manage',
            'label' => 'Manage courses',
            'group' => 'courses',
        ]);
        $coursesView = Permission::create([
            'name' => 'courses.view',
            'label' => 'View courses',
            'group' => 'courses',
        ]);

        $ownerRole->permissions()->attach([$academyView->id, $teacherManage->id, $coursesManage->id, $coursesView->id]);
        $teacherRole->permissions()->attach([$coursesManage->id, $coursesView->id]);

        $owner->roles()->attach($ownerRole->id);
        $teacher->roles()->attach($teacherRole->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $academy->users()->attach($teacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        TeacherProfile::create([
            'user_id' => $teacher->id,
            'specialization' => 'فیزیک',
            'experience_years' => 8,
            'is_verified' => true,
            'is_public' => true,
        ]);

        if (!$withCourse) {
            return [$owner, $academy, $teacher];
        }

        $course = $academy->courses()->create([
            'created_by' => $owner->id,
            'title' => 'دوره تست',
            'slug' => 'course-' . Str::random(8),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);

        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        return [$owner, $academy, $teacher, $course];
    }
}
