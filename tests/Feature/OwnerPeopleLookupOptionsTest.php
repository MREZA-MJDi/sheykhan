<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\OwnerWorkspaceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerPeopleLookupOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_course_and_classroom_options_are_searchable_and_paginated(): void
    {
        [$owner, $academy] = $this->workspace();

        for ($index = 1; $index <= 23; $index++) {
            $teacher = User::factory()->create([
                'name' => 'مدرس گزینه ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
            $academy->users()->attach($teacher->id, [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $course = Course::query()->create([
                'academy_id' => $academy->id,
                'created_by' => $owner->id,
                'title' => 'دوره گزینه ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'slug' => 'lookup-course-' . Str::lower(Str::random(10)),
                'status' => 'draft',
                'access_type' => 'free',
                'price' => 0,
            ]);

            Classroom::query()->create([
                'academy_id' => $academy->id,
                'course_id' => $course->id,
                'title' => 'کلاس گزینه ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'code' => 'LOOK-' . Str::upper(Str::random(8)),
                'status' => 'active',
            ]);
        }

        $teachers = $this->actingAs($owner)->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'teachers',
            'q' => 'مدرس گزینه',
        ]));

        $teachers->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 23)
            ->assertJsonPath('meta.last_page', 2);

        $courses = $this->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'courses',
            'q' => 'دوره گزینه',
            'page' => 2,
        ]));

        $courses->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 2);

        $courseId = (int) $courses->json('data.0.id');
        $classrooms = $this->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'classrooms',
            'q' => 'کلاس گزینه',
            'course_id' => $courseId,
        ]));

        $classrooms->assertOk()->assertJsonCount(1, 'data');
        $this->assertStringContainsString('کلاس گزینه', (string) $classrooms->json('data.0.label'));
    }

    public function test_owner_people_directory_query_count_is_bounded_by_lists_not_member_count(): void
    {
        [$owner, $academy] = $this->workspace();

        foreach (['teacher', 'student', 'parent'] as $role) {
            for ($index = 1; $index <= 30; $index++) {
                $member = User::factory()->create([
                    'name' => $role . ' directory member ' . $index,
                    'status' => 'active',
                ]);
                $academy->users()->attach($member->id, [
                    'role' => $role,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }
        }

        $selects = 0;
        DB::listen(function ($query) use (&$selects): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $selects++;
            }
        });

        $people = app(OwnerWorkspaceService::class)->people($owner, $academy);

        $this->assertSame(30, $people['teachers']->total());
        $this->assertSame(30, $people['students']->total());
        $this->assertSame(30, $people['parents']->total());
        $this->assertSame(10, $people['teachers']->perPage());
        $this->assertLessThan(60, $selects, 'Member directory queries must not grow one query per displayed member.');
    }

    public function test_lookup_options_require_academy_access_and_reject_unknown_types(): void
    {
        [$owner, $academy] = $this->workspace();

        $this->actingAs($owner)->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'unknown',
            'q' => 'مدرس',
        ]))->assertNotFound();

        // Having the academy-owner role is not enough: the owner must own
        // this exact academy, not merely know its identifier.
        $otherOwner = User::factory()->create(['status' => 'active']);
        $otherOwner->roles()->attach($owner->roles()->firstOrFail()->id);

        $this->actingAs($otherOwner)->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'teachers',
            'q' => 'مدرس',
        ]))->assertNotFound();

        $unprivileged = User::factory()->create(['status' => 'active']);
        $this->actingAs($unprivileged)->getJson(route('owner.people.lookup-options', [
            'academy' => $academy,
            'type' => 'teachers',
            'q' => 'مدرس',
        ]))->assertForbidden();
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['status' => 'active']);

        $role = Role::query()->create([
            'name' => 'مدیر جست‌وجوی اعضا',
            'slug' => 'academy-owner',
            'description' => 'Owner lookup options test',
        ]);
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'academy.view'],
            ['label' => 'مشاهده آموزشگاه', 'group' => 'academy'],
        );
        $role->permissions()->sync([$permission->id]);
        $owner->roles()->attach($role->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آموزشگاه جست‌وجو',
            'slug' => 'lookup-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        return [$owner, $academy];
    }
}
