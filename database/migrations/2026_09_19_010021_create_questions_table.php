<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('multiple_choice')->index();
            $table->longText('question');
            $table->json('options')->nullable();
            $table->string('correct_answer')->nullable();
            $table->decimal('score', 8, 2)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['exam_id','sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('questions'); }
};
