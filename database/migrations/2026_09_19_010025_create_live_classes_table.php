<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('live_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recording_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('provider')->nullable();
            $table->string('meeting_url')->nullable();
            $table->timestamp('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);
            $table->string('status')->default('scheduled')->index();
            $table->timestamps();
            $table->index(['course_id','classroom_id','scheduled_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('live_classes'); }
};
