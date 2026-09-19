<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('type')->default('video')->index();
            $table->string('summary', 500)->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->boolean('is_free')->default(false);
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['course_section_id','slug']);
            $table->index(['course_section_id','sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('lessons'); }
};
