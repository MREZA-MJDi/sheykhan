<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\User;
use App\Services\TeacherWorkspaceService;
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

        // One ownership query + one batched active-membership query.
        // No per-student membership lookup or existence query.
        $this->assertLessThanOrEqual(3, $selects);
        $this->assertDatabaseCount('attendances', $before + 2);
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
