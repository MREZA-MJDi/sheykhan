<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'legacy_source')) {
                $table->string('legacy_source', 40)->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'legacy_id')) {
                $table->unsignedBigInteger('legacy_id')->nullable()->after('legacy_source');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['legacy_source', 'legacy_id'], 'users_legacy_source_id_unique');
        });
    }

    public function down(): void
    {
        // Keep legacy provenance columns non-destructive for imported accounts.
    }
};
