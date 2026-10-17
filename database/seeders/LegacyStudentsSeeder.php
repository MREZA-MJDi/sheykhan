<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class LegacyStudentsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            throw new RuntimeException(
                'Real legacy students must never be imported into the automated test database. Use TestFixtureSeeder.'
            );
        }

        if (! config('legacy_students.import_on_db_seed')) {
            throw new RuntimeException(
                'Legacy student import is disabled. Set SHEYKHAN_IMPORT_LEGACY_STUDENTS=true intentionally before running this seeder.'
            );
        }

        $source = (string) config('legacy_students.source_path');
        $academyId = filter_var(config('legacy_students.academy_id'), FILTER_VALIDATE_INT);

        if ($source === '' || ! $academyId) {
            throw new RuntimeException(
                'Set SHEYKHAN_LEGACY_STUDENTS_SQL_PATH and SHEYKHAN_LEGACY_ACADEMY_ID before importing legacy students.'
            );
        }

        $sourceRealPath = realpath($source);
        $publicRealPath = realpath(public_path());

        if (
            $sourceRealPath === false
            || ! is_file($sourceRealPath)
            || ! is_readable($sourceRealPath)
        ) {
            throw new RuntimeException('The private legacy students SQL source does not exist or is not readable.');
        }

        if (
            $publicRealPath !== false
            && str_starts_with($sourceRealPath, rtrim($publicRealPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
        ) {
            throw new RuntimeException('The legacy SQL source must be stored outside public/.');
        }

        // Reuse the one validated importer: it allow-lists exactly the approved
        // 55 legacy IDs and ignores every other statement in the SQL dump.
        // No cleanup of unlisted users is enabled by this seeder.
        $exitCode = Artisan::call('sheykhan:import-legacy-students', [
            'source' => $sourceRealPath,
            '--academy-id' => $academyId,
            '--apply' => true,
        ]);
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            throw new RuntimeException(
                "Legacy student import failed; transaction was not reported as successful.\n" . $output
            );
        }

        if ($this->command && $output !== '') {
            $this->command->line($output);
        }
    }
}
