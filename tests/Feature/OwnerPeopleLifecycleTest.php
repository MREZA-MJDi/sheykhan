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
use Illuminate\Support\Facades\Schema;
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
            ->assertSee('موقتاً غیرفعال');
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
            ->assertSee('آرشیو عضویت')
            ->assertDontSee('student-30@test.local');

        $this->assertStringContainsString('students_page=', $response->getContent());

        $searchUrl = route('owner.people.student-options', $academy);
        $this->actingAs($owner)
            ->get($searchUrl . '?q=student-30%40test.local')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissingPath('data.0.email');

        $this->actingAs($owner)
            ->get($searchUrl . '?q=student-')
            ->assertOk()
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(20, 'data');

        $this->actingAs($owner)
            ->get($searchUrl . '?q=student-&page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(10, 'data')
            ->assertJsonMissingPath('data.9.email');
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
        ]);
        $this->assertSame(1, $classroom->teachers()->count());
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
