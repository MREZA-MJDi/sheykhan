<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('must_change_password')->default(false)->after('password');
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: do not silently remove account security state.
    }
};
