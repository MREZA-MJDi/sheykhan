<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Legacy student activation/import must be an explicit, reviewed operation.
     *
     * Do not mutate user status, role assignments, profiles, or delete records
     * from a migration. The dedicated sheykhan:import-legacy-students command
     * performs a dry-run by default and requires --apply for data changes.
     */
    public function up(): void
    {
        // Intentionally no data mutations. See ImportLegacyStudents command.
    }

    public function down(): void
    {
        // Intentionally non-destructive.
    }
};
