<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spot_player_licenses', function (Blueprint $table): void {
            $table->string('provider_course_id', 120)->nullable()->after('course_id');
            $table->string('provider_mode', 24)->nullable()->after('provider_course_id');
        });
    }

    public function down(): void
    {
        Schema::table('spot_player_licenses', function (Blueprint $table): void {
            $table->dropColumn(['provider_mode', 'provider_course_id']);
        });
    }
};
