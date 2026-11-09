<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\User;
use App\Services\TeacherWorkspaceService;
use App\Services\TeacherDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherWorkspaceNPlusOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_attendance_validates_all_students_with_one_membership_query(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::query()
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->firstOrFail();

        $studentIds = $classroom->students()
            ->wherePivot('status', 'active')
            ->limit(2)
            ->pluck('users.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertCount(2, $studentIds);

        $selects = 0;
        DB::listen(function ($query) use (&$selects): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $selects++;
            }
        });

        $before = Attendance::count();

        app(TeacherWorkspaceService::class)->markAttendance(
            $teacher,
            $classroom,
            array_fill_keys($studentIds, 'present'),
            '2026-10-07',
        );

        // Ownership + batched roster + weekly schedule duration; no per-student queries.
        $this->assertLessThanOrEqual(4, $selects);
        $this->assertDatabaseCount('attendances', $before + 2);
    }
    public function test_teacher_dashboard_query_count_does_not_grow_per_classroom(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();
        $course = $teacher->taughtCourses()->firstOrFail();

        $selects = 0;
        $queries = [];
        $capture = false;
        DB::listen(function ($query) use (&$selects, &$queries, &$capture): void {
            if ($capture && str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $selects++;
                $queries[] = $query->sql;
            }
        });

        $capture = true;
        app(TeacherDashboardService::class)->build($teacher);
        $baseline = $selects;
        $baselineQueryCount = count($queries);
        $capture = false;

        for ($index = 1; $index <= 40; $index++) {
            $classroom = Classroom::query()->create([
                'academy_id' => $course->academy_id,
                'course_id' => $course->id,
                'title' => 'کلاس مقیاس ' . $index,
                'code' => 'SCALE-' . strtoupper(bin2hex(random_bytes(4))),
                'status' => 'active',
            ]);
            $classroom->teachers()->attach($teacher->id);
        }

        $capture = true;
        app(TeacherDashboardService::class)->build($teacher);
        $after = $selects - $baseline;
        $addedQueries = array_slice($queries, $baselineQueryCount);
        $capture = false;

        $this->assertLessThanOrEqual(
            8,
            $after,
            "Adding many classrooms must not add one or more dashboard queries per classroom. Added SQL:\n"
                . implode("\n", $addedQueries)
        );
    }

    public function test_teacher_class_lookup_is_paginated_instead_of_loading_every_class(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();
        $course = $teacher->taughtCourses()->firstOrFail();

        for ($index = 1; $index <= 23; $index++) {
            $classroom = Classroom::query()->create([
                'academy_id' => $course->academy_id,
                'course_id' => $course->id,
                'title' => 'کلاس جست‌وجو ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'code' => 'LOOK-' . strtoupper(bin2hex(random_bytes(4))),
                'status' => 'active',
            ]);
            $classroom->teachers()->attach($teacher->id);
        }

        $response = $this->actingAs($teacher)->getJson(route('teacher.lookup-options', [
            'type' => 'classrooms',
            'q' => 'کلاس جست‌وجو',
        ]));

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 23)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_teacher_lookup_options_require_the_matching_view_permission(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $role = $teacher->roles()->firstOrFail();
        $permissionIds = Permission::query()
            ->whereIn('name', ['courses.view', 'classrooms.view'])
            ->pluck('id');
        $role->permissions()->detach($permissionIds);

        $this->actingAs($teacher)->getJson(route('teacher.lookup-options', [
            'type' => 'courses',
            'q' => 'ریاضی',
        ]))->assertForbidden();

        $this->getJson(route('teacher.lookup-options', [
            'type' => 'classrooms',
            'q' => 'کلاس',
        ]))->assertForbidden();
    }

    public function test_paginated_roster_uses_only_current_active_academy_memberships(): void
    {
        $this->seed();

        $teacher = User::query()
            ->where('email', 'teacher.math@sheykhan.test')
            ->firstOrFail();

        $service = app(TeacherWorkspaceService::class);
        $page = $service->studentsPaginated($teacher);

        $activeClassroomIds = $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->pluck('classrooms.id');

        $expectedStudentIds = User::query()
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $activeClassroomIds)
                ->where('classroom_student.status', 'active'))
            ->orderBy('name')
            ->orderBy('users.id')
            ->limit(20)
            ->pluck('users.id')
            ->all();

        $this->assertGreaterThan(0, $activeClassroomIds->count());
        $this->assertSame($expectedStudentIds, $page->getCollection()->pluck('id')->all());
    }
}
