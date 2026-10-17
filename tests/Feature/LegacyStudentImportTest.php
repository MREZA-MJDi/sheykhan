<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyStudentImportTest extends TestCase
{
    use RefreshDatabase;

    private const APPROVED_LEGACY_IDS = [
        8, 58, 101, 109, 110, 112, 113, 114, 115, 116, 117, 118, 119, 120,
        121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134,
        135, 136, 137, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149,
        150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162,
    ];

    public function test_import_is_dry_run_by_default_and_never_touches_unlisted_legacy_users(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->seed(\Database\Seeders\AcademicReferenceSeeder::class);
        $this->seed(\Database\Seeders\AcademyFoundationSeeder::class);
        $academyId = (int) DB::table('academies')->where('slug', 'sheykhan-academy')->value('id');
        $this->assertGreaterThan(0, $academyId);

        $this->createUnrelatedLegacyUser();
        $path = $this->makeSourceSql();

        try {
            $this->artisan('sheykhan:import-legacy-students', [
                'source' => $path,
                '--academy-id' => $academyId,
            ])->assertExitCode(0);

            $this->assertSame(
                0,
                DB::table('users')->where('legacy_source', 'wordpress')->whereIn('legacy_id', self::APPROVED_LEGACY_IDS)->count()
            );

            $this->assertDatabaseHas('users', [
                'legacy_source' => 'wordpress',
                'legacy_id' => 9000,
                'status' => 'active',
            ]);

            $this->artisan('sheykhan:import-legacy-students', [
                'source' => $path,
                '--academy-id' => $academyId,
                '--apply' => true,
            ])->assertExitCode(0);

            $this->assertSame(
                55,
                DB::table('users')->where('legacy_source', 'wordpress')->whereIn('legacy_id', self::APPROVED_LEGACY_IDS)->count()
            );

            $this->assertSame(
                55,
                DB::table('student_profiles')
                    ->where('registration_source', 'legacy_import')
                    ->where('status', 'active')
                    ->count()
            );

            $studentRoleId = Role::where('slug', 'student')->value('id');
            $importedUserIds = DB::table('users')
                ->select('id')
                ->where('legacy_source', 'wordpress')
                ->whereIn('legacy_id', self::APPROVED_LEGACY_IDS);

            $this->assertSame(
                55,
                DB::table('role_user')
                    ->where('role_id', $studentRoleId)
                    ->whereIn('user_id', $importedUserIds)
                    ->count()
            );

            $this->assertSame(
                55,
                DB::table('academy_user')
                    ->where('academy_id', $academyId)
                    ->where('role', 'student')
                    ->where('status', 'active')
                    ->whereIn('user_id', DB::table('users')
                        ->select('id')
                        ->where('legacy_source', 'wordpress')
                        ->whereIn('legacy_id', self::APPROVED_LEGACY_IDS))
                    ->count()
            );

            // An unrelated imported user outside the source list must retain
            // their account status, student role and profile untouched.
            $this->assertDatabaseHas('users', [
                'legacy_source' => 'wordpress',
                'legacy_id' => 9000,
                'status' => 'active',
            ]);
            $this->assertDatabaseHas('student_profiles', [
                'user_id' => DB::table('users')->where('legacy_source', 'wordpress')->where('legacy_id', 9000)->value('id'),
                'registration_source' => 'admin',
                'status' => 'active',
            ]);

            $this->assertSame(
                55,
                DB::table('academy_user')
                    ->where('academy_id', $academyId)
                    ->where('role', 'student')
                    ->where('status', 'active')
                    ->whereIn('user_id', DB::table('users')
                        ->select('id')
                        ->where('legacy_source', 'wordpress')
                        ->whereIn('legacy_id', self::APPROVED_LEGACY_IDS))
                    ->count()
            );

            // A second run must not duplicate profiles, users, role links or memberships.
            $this->artisan('sheykhan:import-legacy-students', [
                'source' => $path,
                '--academy-id' => $academyId,
                '--apply' => true,
            ])->assertExitCode(0);

            $this->assertSame(
                55,
                DB::table('users')->where('legacy_source', 'wordpress')->whereIn('legacy_id', self::APPROVED_LEGACY_IDS)->count()
            );
            $this->assertSame(
                55,
                DB::table('student_profiles')
                    ->where('registration_source', 'legacy_import')
                    ->where('status', 'active')
                    ->count()
            );
        } finally {
            @unlink($path);
        }
    }

    private function createUnrelatedLegacyUser(): void
    {
        $now = now();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Unrelated historical account',
            'email' => null,
            'password' => password_hash('unrelated-password', PASSWORD_BCRYPT),
            'mobile' => null,
            'status' => 'active',
            'legacy_source' => 'wordpress',
            'legacy_id' => 9000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $studentRoleId = Role::where('slug', 'student')->value('id');
        DB::table('role_user')->insert([
            'role_id' => $studentRoleId,
            'user_id' => $userId,
        ]);

        DB::table('student_profiles')->insert([
            'user_id' => $userId,
            'student_number' => null,
            'birth_date' => null,
            'grade' => null,
            'school_name' => null,
            'bio' => null,
            'registration_source' => 'admin',
            'registered_by' => null,
            'onboarded_at' => null,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function makeSourceSql(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'sheykhan-legacy-import-');

        if ($path === false) {
            $this->fail('Could not create a temporary legacy import fixture.');
        }

        $statements = [];
        foreach (self::APPROVED_LEGACY_IDS as $index => $legacyId) {
            $name = $index === 1 ? 'Student (test case)' : 'Imported student ' . $index;
            // Exercise both supported WordPress hash families without using any
            // of the actual private source's personal data or hashes.
            $hash = $index === 1
                ? '$P$B12345678abcdefghijklmnopqrstuv'
                : '$wp$2y$10$' . str_repeat(chr(65 + ($index % 26)), 53);

            $statements[] = "INSERT INTO users (name, email, password, mobile, status, legacy_source, legacy_id, created_at, updated_at) VALUES ('{$name}', NULL, '{$hash}', NULL, 'active', 'wordpress', {$legacyId}, '2025-01-01 00:00:00', '2025-01-01 00:00:00') ON DUPLICATE KEY UPDATE name=VALUES(name), email=VALUES(email), password=VALUES(password), status='active', updated_at=VALUES(updated_at);";
        }

        file_put_contents($path, implode("\n", $statements));

        return $path;
    }
}
