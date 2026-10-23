<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // In MySQL/MariaDB, the foreign key may already have created a usable
        // single-column index. Add one explicitly only on engines that lack it.
        if (! Schema::hasIndex('parent_student', ['student_id'])) {
            Schema::table('parent_student', function (Blueprint $table): void {
                $table->index('student_id', 'parent_student_student_id_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('parent_student', 'parent_student_student_id_idx')) {
            Schema::table('parent_student', function (Blueprint $table): void {
                $table->dropIndex('parent_student_student_id_idx');
            });
        }
    }
};
