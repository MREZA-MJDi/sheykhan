<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Classroom;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerCourseAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_classrooms_page_renders_with_bound_academy_in_sidebar(): void
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permission = Permission::create([
            'name' => 'classrooms.view',
            'label' => 'View classrooms',
            'group' => 'classrooms',
        ]);

        $role->permissions()->attach($permission->id);

        $owner = User::factory()->create(['email' => 'classrooms-owner-' . Str::random(6) . '@test.local']);
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی کلاس‌ها',
            'slug' => 'classrooms-academy-' . Str::random(6),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره کلاس‌ها',
            'slug' => 'classrooms-course-' . Str::random(6),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);

        Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس تست',
            'code' => 'CLS-' . Str::upper(Str::random(6)),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.classrooms.index', $academy))
            ->assertOk();
    }

    public function test_owner_course_create_edit_and_update_do_not_use_pivot_column_in_academy_lookup(): void
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permission = Permission::create([
            'name' => 'courses.manage',
            'label' => 'Manage courses',
            'group' => 'courses',
        ]);

        $role->permissions()->attach($permission->id);

        $owner = User::factory()->create(['email' => 'course-owner-' . Str::random(6) . '@test.local']);
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی دوره‌ها',
            'slug' => 'course-academy-' . Str::random(6),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.courses.create'))
            ->assertOk();

        $this->actingAs($owner)
            ->post(route('owner.courses.store'), [
                'academy_id' => $academy->id,
                'title' => 'دوره جدید',
                'slug' => 'new-course-' . Str::random(6),
                'status' => 'draft',
                'access_type' => 'free',
                'price' => 0,
                'duration_minutes' => 60,
            ])
            ->assertSessionHas('success');

        $course = Course::query()->where('academy_id', $academy->id)->firstOrFail();

        $this->actingAs($owner)
            ->get(route('owner.courses.edit', $course))
            ->assertOk();

        $this->actingAs($owner)
            ->patch(route('owner.courses.update', $course), [
                'academy_id' => $academy->id,
                'title' => 'دوره به‌روزشده',
                'slug' => $course->slug,
                'status' => 'draft',
                'access_type' => 'free',
                'price' => 0,
                'duration_minutes' => 90,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'دوره به‌روزشده',
            'duration_minutes' => 90,
        ]);
    }

    public function test_owner_cannot_open_a_course_from_another_owner(): void
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);
        $view = Permission::create([
            'name' => 'courses.view',
            'label' => 'View courses',
            'group' => 'courses',
        ]);
        $manage = Permission::create([
            'name' => 'courses.manage',
            'label' => 'Manage courses',
            'group' => 'courses',
        ]);
        $role->permissions()->attach([$view->id, $manage->id]);

        $ownerA = User::factory()->create(['email' => 'a@test.local']);
        $ownerB = User::factory()->create(['email' => 'b@test.local']);
        $ownerA->roles()->attach($role->id);
        $ownerB->roles()->attach($role->id);

        $academyB = Academy::create([
            'owner_id' => $ownerB->id,
            'name' => 'آکادمی B',
            'slug' => 'academy-b-' . Str::random(5),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academyB->id,
            'created_by' => $ownerB->id,
            'title' => 'دوره B',
            'slug' => 'course-b-' . Str::random(5),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);

        $this->actingAs($ownerA)
            ->get(route('owner.courses.show', $course))
            ->assertForbidden();
    }
}