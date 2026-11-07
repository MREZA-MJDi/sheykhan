<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->index(['academy_id', 'title'], 'courses_academy_title_lookup_idx');
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->index(['academy_id', 'title'], 'classrooms_academy_title_lookup_idx');
            $table->index(['academy_id', 'course_id', 'title'], 'classrooms_academy_course_title_idx');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->index(['course_section_id', 'title'], 'lessons_section_title_lookup_idx');
        });

        Schema::table('protected_file_views', function (Blueprint $table): void {
            $table->index(['viewed_at', 'id'], 'pfv_retention_time_idx');
        });

        Schema::table('protected_learning_media_views', function (Blueprint $table): void {
            $table->index(['academy_id', 'viewed_at', 'id'], 'plmv_academy_time_idx');
        });

        Schema::table('spot_player_playback_views', function (Blueprint $table): void {
            $table->index(['viewed_at', 'id'], 'spv_retention_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('spot_player_playback_views', function (Blueprint $table): void {
            $table->dropIndex('spv_retention_time_idx');
        });

        Schema::table('protected_learning_media_views', function (Blueprint $table): void {
            $table->dropIndex('plmv_academy_time_idx');
        });

        Schema::table('protected_file_views', function (Blueprint $table): void {
            $table->dropIndex('pfv_retention_time_idx');
        });

        Schema::table('lessons', function (Blueprint $table): void {
            $table->dropIndex('lessons_section_title_lookup_idx');
        });

        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropIndex('classrooms_academy_course_title_idx');
            $table->dropIndex('classrooms_academy_title_lookup_idx');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_academy_title_lookup_idx');
        });
    }
};
