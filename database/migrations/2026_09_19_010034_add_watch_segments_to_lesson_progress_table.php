<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->unsignedInteger('last_position_seconds')
                ->default(0)
                ->after('seconds_watched');

            $table->json('watched_segments')
                ->nullable()
                ->after('last_position_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_progress', function (Blueprint $table): void {
            $table->dropColumn(['last_position_seconds', 'watched_segments']);
        });
    }
};
