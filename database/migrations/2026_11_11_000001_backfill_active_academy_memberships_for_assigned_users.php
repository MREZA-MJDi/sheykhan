<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Teachers already assigned to a course/classroom are legitimate academy
        // members. Older records may predate the academy_user membership gate.
        $teacherAssignments = DB::table('course_teacher as assignment')
            ->join('courses', 'courses.id', '=', 'assignment.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'courses.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('users', function ($join): void {
                $join->on('users.id', '=', 'assignment.teacher_id')
                    ->where('users.status', '=', 'active')
                    ->whereNull('users.deleted_at');
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'role_user.role_id')
                    ->where('roles.slug', '=', 'teacher');
            })
            ->select('courses.academy_id', 'users.id as user_id')
            ->distinct()
            ->get();

        $classroomTeachers = DB::table('classroom_teacher as assignment')
            ->join('classrooms', 'classrooms.id', '=', 'assignment.classroom_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'classrooms.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('users', function ($join): void {
                $join->on('users.id', '=', 'assignment.teacher_id')
                    ->where('users.status', '=', 'active')
                    ->whereNull('users.deleted_at');
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'role_user.role_id')
                    ->where('roles.slug', '=', 'teacher');
            })
            ->select('classrooms.academy_id', 'users.id as user_id')
            ->distinct()
            ->get();

        foreach ($teacherAssignments->merge($classroomTeachers)->unique(fn ($row) => $row->academy_id . ':' . $row->user_id) as $member) {
            DB::table('academy_user')->insertOrIgnore([
                'academy_id' => $member->academy_id,
                'user_id' => $member->user_id,
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Active enrollments are the source of truth for student-course access.
        // Backfill only missing membership rows; an explicitly archived membership
        // remains archived because the composite unique key makes this insert a no-op.
        $studentEnrollments = DB::table('course_enrollments as enrollment')
            ->join('courses', 'courses.id', '=', 'enrollment.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'courses.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('users', function ($join): void {
                $join->on('users.id', '=', 'enrollment.student_id')
                    ->where('users.status', '=', 'active')
                    ->whereNull('users.deleted_at');
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'role_user.role_id')
                    ->where('roles.slug', '=', 'student');
            })
            ->where('enrollment.status', '=', 'active')
            ->select('courses.academy_id', 'users.id as user_id')
            ->distinct()
            ->get();

        foreach ($studentEnrollments as $member) {
            DB::table('academy_user')->insertOrIgnore([
                'academy_id' => $member->academy_id,
                'user_id' => $member->user_id,
                'role' => 'student',
                'status' => 'active',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Membership backfills are additive and intentionally not rolled back.
    }
};
