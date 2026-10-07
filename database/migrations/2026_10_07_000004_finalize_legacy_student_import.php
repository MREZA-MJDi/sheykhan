<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    /**
     * The verified legacy WordPress/Tutor student identities.
     *
     * Only legacy_source + legacy_id are used as identity; current Laravel
     * auto-increment IDs are intentionally ignored.
     */
    private const VERIFIED_LEGACY_IDS = [
        8, 58, 101, 109, 110, 112, 113, 114, 115, 116, 117, 118, 119,
        120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132,
        133, 134, 135, 136, 137, 139, 140, 141, 142, 143, 144, 145, 146,
        147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159,
        160, 161, 162,
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('users', 'legacy_source')
            || ! Schema::hasColumn('users', 'legacy_id')) {
            return;
        }

        DB::transaction(function (): void {
            $studentRoleId = DB::table('roles')
                ->where('slug', 'student')
                ->value('id');

            if (! $studentRoleId) {
                return;
            }

            $legacyQuery = DB::table('users')
                ->where('legacy_source', 'wordpress');

            $excludedIds = (clone $legacyQuery)
                ->whereNotIn('legacy_id', self::VERIFIED_LEGACY_IDS)
                ->pluck('id');

            if ($excludedIds->isNotEmpty()) {
                DB::table('role_user')
                    ->where('role_id', $studentRoleId)
                    ->whereIn('user_id', $excludedIds)
                    ->delete();

                if (Schema::hasColumn('student_profiles', 'registration_source')) {
                    DB::table('student_profiles')
                        ->whereIn('user_id', $excludedIds)
                        ->where('registration_source', 'legacy_import')
                        ->delete();
                }

                // Do not hard-delete legacy identities here: other legacy
                // relations may exist. Block them until a dependency audit
                // explicitly authorizes physical deletion.
                DB::table('users')
                    ->whereIn('id', $excludedIds)
                    ->update([
                        'status' => 'blocked',
                        'updated_at' => now(),
                    ]);
            }

            $verifiedUsers = (clone $legacyQuery)
                ->whereIn('legacy_id', self::VERIFIED_LEGACY_IDS)
                ->pluck('id');

            if ($verifiedUsers->isEmpty()) {
                return;
            }

            DB::table('users')
                ->whereIn('id', $verifiedUsers)
                ->update([
                    'status' => 'active',
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);

            foreach ($verifiedUsers as $userId) {
                DB::table('role_user')->updateOrInsert(
                    [
                        'role_id' => $studentRoleId,
                        'user_id' => $userId,
                    ],
                    []
                );
            }

            if (Schema::hasColumn('student_profiles', 'status')) {
                DB::table('student_profiles')
                    ->whereIn('user_id', $verifiedUsers)
                    ->update(['status' => 'active', 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. The legacy import is source data and
        // must not be silently restored/deleted by a migration rollback.
    }
};
