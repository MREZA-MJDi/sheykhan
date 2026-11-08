<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'mobile')) {
            return;
        }

        if (Schema::hasIndex('users', 'users_mobile_unique')) {
            return;
        }

        $hasDuplicates = DB::table('users')
            ->select('mobile')
            ->whereNotNull('mobile')
            ->where('mobile', '!=', '')
            ->groupBy('mobile')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            Log::warning('Mobile unique index was not created because duplicate mobile values already exist. Resolve duplicates before retrying the migration.');
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('mobile', 'users_mobile_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasIndex('users', 'users_mobile_unique')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_mobile_unique');
            });
        }
    }
};
