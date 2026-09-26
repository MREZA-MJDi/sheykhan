<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerPeopleLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_archive_and_restore_teacher_without_deleting_history(): void
    {
        [$owner, $academy, $teacher] = $this->makeWorkspace();

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

        $ownerRole->permissions()->attach([$teacherManage->id, $coursesManage->id, $coursesView->id]);
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
