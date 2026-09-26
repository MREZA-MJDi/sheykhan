<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_user', function (Blueprint $table): void {
            $table->index(
                ['academy_id', 'role', 'status'],
                'academy_user_academy_role_status_idx'
            );
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->index(
                ['academy_id', 'status', 'updated_at'],
                'courses_academy_status_updated_idx'
            );
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->index(
                ['academy_id', 'status', 'starts_at'],
                'classrooms_academy_status_starts_idx'
            );
        });

        Schema::table('course_sections', function (Blueprint $table): void {
            $table->index(
                ['course_id', 'sort_order'],
                'course_sections_course_sort_idx'
            );
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->index(
                ['course_section_id', 'status', 'published_at'],
                'lessons_section_status_published_idx'
            );
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'lesson_id', 'last_watched_at'],
                'lesson_progress_user_lesson_watched_idx'
            );
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->index(
                ['course_id', 'status', 'student_id'],
                'course_enrollments_course_status_student_idx'
            );
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->index(
                ['exam_id', 'student_id', 'attempt_number', 'submitted_at'],
                'exam_attempts_latest_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropIndex('exam_attempts_latest_lookup_idx');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropIndex('course_enrollments_course_status_student_idx');
        });

        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->dropIndex('lesson_progress_user_lesson_watched_idx');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropIndex('lessons_section_status_published_idx');
        });

        Schema::table('course_sections', function (Blueprint $table): void {
            $table->dropIndex('course_sections_course_sort_idx');
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropIndex('classrooms_academy_status_starts_idx');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_academy_status_updated_idx');
        });

        Schema::table('academy_user', function (Blueprint $table): void {
            $table->dropIndex('academy_user_academy_role_status_idx');
        });
    }
};
