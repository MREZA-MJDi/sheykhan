<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->index(['student_id', 'status', 'classroom_id'], 'classroom_student_student_status_classroom_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->index(['teacher_id', 'classroom_id'], 'classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->index(['classroom_id', 'status', 'student_id'], 'course_enrollments_classroom_status_student_idx');
            $table->index(['course_id', 'status'], 'course_enrollments_course_status_idx');
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->index(['classroom_id', 'attendance_date'], 'attendances_classroom_date_idx');
            $table->index(['student_id', 'attendance_date'], 'attendances_student_date_idx');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->index(['assignment_id', 'submitted_at', 'graded_at'], 'assignment_submissions_review_queue_idx');
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->index(['user_id', 'lesson_id'], 'lesson_progress_user_lesson_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->dropIndex('lesson_progress_user_lesson_idx');
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            $table->dropIndex('assignment_submissions_review_queue_idx');
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex('attendances_classroom_date_idx');
            $table->dropIndex('attendances_student_date_idx');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropIndex('course_enrollments_classroom_status_student_idx');
            $table->dropIndex('course_enrollments_course_status_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->dropIndex('classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->dropIndex('classroom_student_student_status_classroom_idx');
        });
    }
};
