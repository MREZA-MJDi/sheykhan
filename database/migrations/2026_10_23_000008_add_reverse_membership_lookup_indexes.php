<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing operational-index migrations already cover teacher/classroom
        // and student/classroom lookup paths. This adds only the missing reverse
        // tenant-membership lookup used by role-scoped message recipient checks.
        Schema::table('academy_user', function (Blueprint $table): void {
            $table->index(['user_id', 'academy_id'], 'academy_user_user_academy_idx');
        });
    }

    public function down(): void
    {
        Schema::table('academy_user', function (Blueprint $table): void {
            $table->dropIndex('academy_user_user_academy_idx');
        });
    }
};
