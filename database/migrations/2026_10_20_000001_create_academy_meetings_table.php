<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academy_meetings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('scheduled_end_at')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->index(['academy_id', 'status', 'scheduled_at']);
            $table->index(['student_id', 'status', 'scheduled_at']);
            $table->index(['parent_id', 'status', 'scheduled_at']);
            $table->index(['classroom_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_meetings');
    }
};
