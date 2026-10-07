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

        // One ownership lookup + one batched membership lookup + one batch upsert.
        // No per-student membership lookup or existence query.
        $this->assertLessThanOrEqual(3, $selects);
        $this->assertDatabaseCount('attendances', $before + 2);
    }
}
