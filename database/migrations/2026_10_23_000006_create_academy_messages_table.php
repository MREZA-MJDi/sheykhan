<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academy_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_role', 24);
            $table->string('audience', 32);
            $table->foreignId('recipient_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 32)->default('other');
            $table->string('subject', 180);
            $table->text('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'audience', 'created_at'], 'academy_messages_audience_created_idx');
            $table->index(['recipient_id', 'audience', 'created_at'], 'academy_messages_recipient_audience_idx');
            $table->index(['classroom_id', 'audience', 'created_at'], 'academy_messages_classroom_audience_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_messages');
    }
};
