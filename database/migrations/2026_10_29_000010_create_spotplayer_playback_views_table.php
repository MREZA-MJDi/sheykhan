<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_player_playback_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('spot_player_license_id')->constrained('spot_player_licenses')->cascadeOnDelete();
            $table->string('event_type', 24)->default('player_open');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'course_id', 'viewed_at'], 'spv_user_course_time_idx');
            $table->index(['course_id', 'viewed_at'], 'spv_course_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_player_playback_views');
    }
};
