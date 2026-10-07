<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $t->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $t->string('display_name');
            $t->string('achievement_type', 64)->index();
            $t->string('school_name')->nullable();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $t->string('status', 32)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->timestamp('published_at')->nullable()->index();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['academy_id', 'status', 'is_featured', 'published_at']);
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('display_name');
            $t->string('role', 32)->default('parent')->index();
            $t->text('content_text')->nullable();
            $t->string('status', 32)->default('pending')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamp('published_at')->nullable()->index();
            $t->timestamps();
            $t->index(['academy_id', 'status', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('achievements');
    }
};
