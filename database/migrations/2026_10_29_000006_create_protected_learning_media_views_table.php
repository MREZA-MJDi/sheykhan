<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protected_learning_media_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            // Keep every protected-media event scoped to the academy, even when the
            // media is attached directly to a lesson, exam, assignment or recording.
            $table->unsignedBigInteger('academy_id')->nullable()->index();
            // Context IDs allow the admin/owner audit to scope records by academy.
            $table->unsignedBigInteger('learning_resource_id')->nullable()->index();
            $table->unsignedBigInteger('child_id')->nullable()->index();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('event_type', 24)->default('open');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'media_id', 'viewed_at'], 'plmv_user_media_time_idx');
            $table->index(['media_id', 'viewed_at'], 'plmv_media_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protected_learning_media_views');
    }
};
