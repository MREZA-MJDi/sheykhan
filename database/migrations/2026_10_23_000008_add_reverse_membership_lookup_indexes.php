<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academy_user', function (Blueprint $table): void {
            $table->index(['user_id', 'academy_id'], 'academy_user_user_academy_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->index(['teacher_id', 'classroom_id'], 'classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->index(['student_id', 'classroom_id'], 'classroom_student_student_classroom_idx');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_student', function (Blueprint $table): void {
            $table->dropIndex('classroom_student_student_classroom_idx');
        });

        Schema::table('classroom_teacher', function (Blueprint $table): void {
            $table->dropIndex('classroom_teacher_teacher_classroom_idx');
        });

        Schema::table('academy_user', function (Blueprint $table): void {
            $table->dropIndex('academy_user_user_academy_idx');
        });
    }
};
