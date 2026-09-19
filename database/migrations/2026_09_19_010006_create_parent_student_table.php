<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('relation')->nullable();
            $table->timestamps();
            $table->primary(['parent_id', 'student_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('parent_student'); }
};
