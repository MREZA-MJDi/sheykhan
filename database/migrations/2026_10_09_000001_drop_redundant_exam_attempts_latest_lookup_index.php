<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX = 'exam_attempts_latest_lookup_idx';

    public function up(): void
    {
        if (!Schema::hasTable('exam_attempts') || !$this->indexExists()) {
            return;
        }

        // The unique (exam_id, student_id, attempt_number) index already covers
        // the leftmost three columns. The extra submitted_at suffix does not
        // improve the current lookup paths and only duplicates index storage.
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropIndex(self::INDEX);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('exam_attempts') || $this->indexExists()) {
            return;
        }

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->index(
                ['exam_id', 'student_id', 'attempt_number', 'submitted_at'],
                self::INDEX
            );
        });
    }

    private function indexExists(): bool
    {
        foreach (Schema::getIndexes('exam_attempts') as $index) {
            if (($index['name'] ?? null) === self::INDEX) {
                return true;
            }
        }

        return false;
    }
};
