<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->foreignId('source_bank_question_id')
                ->nullable()
                ->after('exam_id')
                ->constrained('question_bank_items')
                ->nullOnDelete();
            $table->index(['exam_id', 'source_bank_question_id'], 'questions_exam_bank_source_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropIndex('questions_exam_bank_source_lookup');
            $table->dropConstrainedForeignId('source_bank_question_id');
        });
    }
};
