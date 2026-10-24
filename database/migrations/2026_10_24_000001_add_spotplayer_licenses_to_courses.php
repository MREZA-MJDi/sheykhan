<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('spotplayer_course_id', 120)->nullable()->after('slug');
        });

        Schema::create('spot_player_licenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('external_license_id', 120)->unique();
            $table->longText('license_key');
            $table->text('license_url');
            $table->string('status', 24)->default('active')->index();
            $table->timestamp('issued_at');
            $table->timestamps();
            $table->unique(['student_id', 'course_id']);
            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_player_licenses');

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('spotplayer_course_id');
        });
    }
};
