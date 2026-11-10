<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_personal_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->longText('content');
            $table->timestamps();

            $table->index(['student_id', 'updated_at'], 'student_personal_notes_owner_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_personal_notes');
    }
};
