<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 32)->default('question');
            $table->string('subject', 180);
            $table->text('body');
            $table->string('status', 32)->default('open')->index();
            $table->text('response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'created_at']);
            $table->index(['parent_id', 'created_at']);
            $table->index(['student_id', 'created_at']);
        });

        Schema::create('counseling_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 180);
            $table->text('details')->nullable();
            $table->timestamp('preferred_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 32)->default('requested')->index();
            $table->text('response_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'created_at']);
            $table->index(['parent_id', 'created_at']);
        });

        Schema::create('teacher_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 180);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 8)->default('IRT');
            $table->string('status', 24)->default('pending')->index();
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'teacher_id', 'created_at']);
            $table->index(['teacher_id', 'status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payments');
        Schema::dropIfExists('counseling_requests');
        Schema::dropIfExists('parent_messages');
    }
};
