<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_player_license_issuances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('provider_course_id', 120);
            $table->string('provider_mode', 24);
            $table->string('status', 24)->default('issuing')->index();
            $table->unsignedSmallInteger('attempt_count')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('last_error', 190)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_player_license_issuances');
    }
};
