<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentTeacherDashboardBugFixTest extends TestCase
{
    public function test_student_live_class_index_requires_the_same_active_teacher_cohort_as_the_join_policy(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Student/LiveClassController.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("->on('eligible_teacher.teacher_id', '=', 'live_classes.teacher_id')", $source);
        $this->assertStringContainsString("->where('eligible_enrollment.status', '=', 'active')", $source);
        $this->assertStringContainsString("->where('eligible_membership.status', 'active')", $source);
        $this->assertStringContainsString("->orWhereColumn('live_classes.classroom_id', 'eligible_classroom.id')", $source);
        $this->assertStringContainsString("->whereColumn('financial_transactions.enrollment_id', 'eligible_enrollment.id')", $source);
    }

    public function test_teacher_dashboard_student_metric_requires_an_active_enrollment_in_the_classroom(): void
    {
        $source = file_get_contents(app_path('Services/TeacherDashboardService.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("->join('course_enrollments as enrollments'", $source);
        $this->assertStringContainsString("->on('enrollments.classroom_id', '=', 'classrooms.id')", $source);
        $this->assertStringContainsString("->on('enrollments.course_id', '=', 'classrooms.course_id')", $source);
        $this->assertStringContainsString("->where('enrollments.status', '=', 'active')", $source);
    }

    public function test_teacher_rosters_exclude_inactive_users_and_memberships_without_active_enrollment(): void
    {
        $source = file_get_contents(app_path('Services/TeacherWorkspaceService.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("->where('users.status', 'active')", $source);
        $this->assertStringContainsString("->whereNull('users.deleted_at')", $source);
        $this->assertStringContainsString("->whereColumn('course_enrollments.classroom_id', 'classrooms.id')", $source);
        $this->assertStringContainsString("->whereColumn('course_enrollments.student_id', 'users.id')", $source);
        $this->assertStringContainsString("->where('course_enrollments.status', 'active')", $source);
    }

    public function test_student_live_class_view_uses_meaningful_statuses_and_persian_weekday_names(): void
    {
        $source = file_get_contents(resource_path('views/student/live-classes/index.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("'live' => 'در حال برگزاری'", $source);
        $this->assertStringContainsString("'published' => 'ضبط منتشرشده'", $source);
        $this->assertStringContainsString("['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه']", $source);
        $this->assertStringNotContainsString('PersianUi::digits($schedule->weekday)', $source);
    }

    public function test_student_dashboard_recent_sessions_are_scoped_to_the_matching_active_teacher_cohort(): void
    {
        $source = file_get_contents(app_path('Services/StudentDashboardService.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("->on('recent_teacher_assignment.teacher_id', '=', 'live_classes.teacher_id')", $source);
        $this->assertStringContainsString("->where('recent_enrollment.status', '=', 'active')", $source);
        $this->assertStringContainsString("->where('recent_student_membership.status', 'active')", $source);
        $this->assertStringContainsString("->orWhereColumn('live_classes.classroom_id', 'recent_classroom.id')", $source);
        $this->assertStringContainsString("->whereColumn('financial_transactions.enrollment_id', 'recent_enrollment.id')", $source);
    }

    public function test_teacher_live_class_view_translates_completed_recording_statuses(): void
    {
        $source = file_get_contents(resource_path('views/teacher/live-classes/index.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("'completed' => ['پایان‌یافته','muted']", $source);
        $this->assertStringContainsString("'published' => ['ضبط منتشرشده','success']", $source);
        $this->assertStringNotContainsString("default => [$liveClass->status,'muted']", $source);
    }
}
