<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->softDeletes();
        });

        // A nullable academic_year_id is useful for legacy imports, but MySQL UNIQUE
        // indexes otherwise allow multiple NULL rows for the same course/student pair.
        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropUnique('course_enrollments_course_student_year_unique');
            $table->unsignedBigInteger('academic_year_key')
                ->storedAs('COALESCE(academic_year_id, 0)')
                ->after('academic_year_id');
            $table->unique(
                ['course_id', 'student_id', 'academic_year_key'],
                'course_enrollments_course_student_year_effective_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropUnique('course_enrollments_course_student_year_effective_unique');
            $table->dropColumn('academic_year_key');
            $table->unique(
                ['course_id', 'student_id', 'academic_year_id'],
                'course_enrollments_course_student_year_unique'
            );
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
