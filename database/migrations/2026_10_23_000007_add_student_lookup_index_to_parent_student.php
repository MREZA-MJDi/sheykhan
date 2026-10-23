<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_student', function (Blueprint $table): void {
            $table->index('student_id', 'parent_student_student_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parent_student', function (Blueprint $table): void {
            $table->dropIndex('parent_student_student_id_idx');
        });
    }
};
