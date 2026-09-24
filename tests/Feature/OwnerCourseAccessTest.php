<?php

namespace TestsFeature;

use AppModelsAcademy;
use AppModelsCourse;
use AppModelsPermission;
use AppModelsRole;
use AppModelsUser;
use IlluminateFoundationTestingRefreshDatabase;
use IlluminateSupportStr;
use TestsTestCase;

class OwnerCourseAccessTest extends TestCase
{
    use RefreshDatabase;

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