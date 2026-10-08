<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->softDeletes();
        });

        // A nullable academic_year_id is useful for legacy imports, but MySQL UNIQUE
        // indexes otherwise allow multiple NULL rows for the same course/student pair.
        //
        // Do not use a generated column here on MySQL. academic_year_id is a nullable
        // foreign key with ON DELETE SET NULL, and MySQL rejects foreign-key actions
        // on a base column of a generated column. MySQL 8.4 supports functional key
        // parts, so enforce the effective uniqueness directly in the index expression.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE course_enrollments DROP INDEX course_enrollments_course_student_year_unique'
            );
            DB::statement(
                'ALTER TABLE course_enrollments ' .
                'ADD UNIQUE INDEX course_enrollments_course_student_year_effective_unique ' .
                '(course_id, student_id, (COALESCE(academic_year_id, 0)))'
            );

            return;
        }

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
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE course_enrollments DROP INDEX course_enrollments_course_student_year_effective_unique'
            );
            DB::statement(
                'ALTER TABLE course_enrollments ' .
                'ADD UNIQUE INDEX course_enrollments_course_student_year_unique ' .
                '(course_id, student_id, academic_year_id)'
            );
        } else {
            Schema::table('course_enrollments', function (Blueprint $table): void {
                $table->dropUnique('course_enrollments_course_student_year_effective_unique');
                $table->dropColumn('academic_year_key');
                $table->unique(
                    ['course_id', 'student_id', 'academic_year_id'],
                    'course_enrollments_course_student_year_unique'
                );
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
