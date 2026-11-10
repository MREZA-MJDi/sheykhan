<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_bank_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 24)->default('text');
            $table->longText('question');
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->longText('expected_answer')->nullable();
            $table->decimal('default_score', 8, 2)->default(1);
            $table->string('topic', 120)->nullable();
            $table->string('difficulty', 16)->default('medium');
            $table->string('status', 16)->default('active')->index();
            $table->timestamps();

            $table->index(['academy_id', 'status', 'type']);
            $table->index(['academy_id', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_items');
    }
};
