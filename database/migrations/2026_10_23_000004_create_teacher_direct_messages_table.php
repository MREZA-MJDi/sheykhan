<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_direct_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_type', 16);
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 32)->default('other');
            $table->string('subject', 180);
            $table->text('body');
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->string('status', 20)->default('sent');
            $table->timestamps();

            $table->index(['teacher_id', 'created_at']);
            $table->index(['recipient_type', 'recipient_id', 'status', 'created_at'], 'teacher_direct_messages_recipient_idx');
            $table->index(['academy_id', 'classroom_id', 'created_at'], 'teacher_direct_messages_context_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_direct_messages');
    }
};
