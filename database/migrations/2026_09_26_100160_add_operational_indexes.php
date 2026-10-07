<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table): void {
            $table->index(['user_id', 'role_id'], 'role_user_user_role_idx');
        });

        Schema::table('permission_role', function (Blueprint $table): void {
            $table->index(['role_id', 'permission_id'], 'permission_role_role_permission_idx');
        });

        Schema::table('academy_user', function (Blueprint $table): void {
            $table->index(['user_id', 'role', 'status'], 'academy_user_user_role_status_idx');
        });

        Schema::table('course_teacher', function (Blueprint $table): void {
            $table->index(['teacher_id', 'course_id'], 'course_teacher_teacher_course_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->index(['teacher_id', 'classroom_id'], 'classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->index(['classroom_id', 'status', 'student_id'], 'classroom_student_class_status_idx');
            $table->index(['student_id', 'status', 'classroom_id'], 'classroom_student_student_status_idx');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->index(['student_id', 'course_id', 'status'], 'course_enrollments_student_course_status_idx');
            $table->index(['classroom_id', 'status', 'student_id'], 'course_enrollments_class_status_idx');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->index(['teacher_id', 'due_at'], 'assignments_teacher_due_idx');
            $table->index(['classroom_id', 'created_at'], 'assignments_classroom_created_idx');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->index(['assignment_id', 'submitted_at', 'graded_at'], 'assignment_submissions_review_idx');
            $table->index(['student_id', 'submitted_at'], 'assignment_submissions_student_submitted_idx');
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->index(['teacher_id', 'starts_at'], 'exams_teacher_starts_idx');
            $table->index(['classroom_id', 'status'], 'exams_classroom_status_idx');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->index(['exam_id', 'status', 'submitted_at'], 'exam_attempts_review_idx');
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->index(['classroom_id', 'attendance_date', 'status'], 'attendances_class_date_status_idx');
            $table->index(['student_id', 'attendance_date'], 'attendances_student_date_idx');
        });

        Schema::table('live_classes', function (Blueprint $table): void {
            $table->index(['teacher_id', 'scheduled_at'], 'live_classes_teacher_schedule_idx');
            $table->index(['classroom_id', 'status', 'scheduled_at'], 'live_classes_class_status_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::table('live_classes', function (Blueprint $table): void {
            $table->dropIndex('live_classes_teacher_schedule_idx');
            $table->dropIndex('live_classes_class_status_schedule_idx');
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex('attendances_class_date_status_idx');
            $table->dropIndex('attendances_student_date_idx');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropIndex('exam_attempts_review_idx');
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropIndex('exams_teacher_starts_idx');
            $table->dropIndex('exams_classroom_status_idx');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->dropIndex('assignment_submissions_review_idx');
            $table->dropIndex('assignment_submissions_student_submitted_idx');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropIndex('assignments_teacher_due_idx');
            $table->dropIndex('assignments_classroom_created_idx');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropIndex('course_enrollments_student_course_status_idx');
            $table->dropIndex('course_enrollments_class_status_idx');
        });

        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->dropIndex('classroom_student_class_status_idx');
            $table->dropIndex('classroom_student_student_status_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->dropIndex('classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('course_teacher', function (Blueprint $table): void {
            $table->dropIndex('course_teacher_teacher_course_idx');
        });

        Schema::table('academy_user', function (Blueprint $table): void {
            $table->dropIndex('academy_user_user_role_status_idx');
        });

        Schema::table('permission_role', function (Blueprint $table): void {
            $table->dropIndex('permission_role_role_permission_idx');
        });

        Schema::table('role_user', function (Blueprint $table): void {
            $table->dropIndex('role_user_user_role_idx');
        });
    }
};
