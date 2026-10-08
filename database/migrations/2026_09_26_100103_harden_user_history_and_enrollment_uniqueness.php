<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const LEGACY_INDEX = 'course_enrollments_course_student_year_unique';
    private const EFFECTIVE_INDEX = 'course_enrollments_course_student_year_effective_unique';
    private const YEAR_KEY = 'academic_year_key';

    public function up(): void
    {
        // DDL can be committed before a failed migration is recorded. Make retries
        // safe when a previous attempt already added deleted_at or dropped the old index.
        if (! Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        $driver = DB::getDriverName();
        $isMariaDb = $driver === 'mysql'
            && str_contains(strtolower((string) DB::selectOne('SELECT VERSION() AS version')?->version), 'mariadb');

        // MySQL 8 supports functional key parts. MariaDB does not support that syntax,
        // so use a stored generated column there. SQLite uses the same portable path.
        if ($driver === 'mysql' && ! $isMariaDb) {
            $this->dropIndexIfExists(self::LEGACY_INDEX);

            if (! $this->indexExists(self::EFFECTIVE_INDEX)) {
                DB::statement(
                    'ALTER TABLE course_enrollments ' .
                    'ADD UNIQUE INDEX ' . self::EFFECTIVE_INDEX . ' ' .
                    '(course_id, student_id, (COALESCE(academic_year_id, 0)))'
                );
            }

            return;
        }

        if (! Schema::hasColumn('course_enrollments', self::YEAR_KEY)) {
            Schema::table('course_enrollments', function (Blueprint $table): void {
                $table->unsignedBigInteger(self::YEAR_KEY)
                    ->storedAs('COALESCE(academic_year_id, 0)')
                    ->after('academic_year_id');
            });
        }

        $this->dropIndexIfExists(self::LEGACY_INDEX);

        if (! $this->indexExists(self::EFFECTIVE_INDEX)) {
            Schema::table('course_enrollments', function (Blueprint $table): void {
                $table->unique(
                    ['course_id', 'student_id', self::YEAR_KEY],
                    self::EFFECTIVE_INDEX
                );
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        $isMariaDb = $driver === 'mysql'
            && str_contains(strtolower((string) DB::selectOne('SELECT VERSION() AS version')?->version), 'mariadb');

        $this->dropIndexIfExists(self::EFFECTIVE_INDEX);

        if (($driver !== 'mysql' || $isMariaDb) && Schema::hasColumn('course_enrollments', self::YEAR_KEY)) {
            Schema::table('course_enrollments', function (Blueprint $table): void {
                $table->dropColumn(self::YEAR_KEY);
            });
        }

        if (! $this->indexExists(self::LEGACY_INDEX)) {
            Schema::table('course_enrollments', function (Blueprint $table): void {
                $table->unique(
                    ['course_id', 'student_id', 'academic_year_id'],
                    self::LEGACY_INDEX
                );
            });
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }

    private function indexExists(string $name): bool
    {
        foreach (Schema::getIndexes('course_enrollments') as $index) {
            if (($index['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }

    private function dropIndexIfExists(string $name): void
    {
        if ($this->indexExists($name)) {
            Schema::table('course_enrollments', function (Blueprint $table) use ($name): void {
                $table->dropUnique($name);
            });
        }
    }
};
