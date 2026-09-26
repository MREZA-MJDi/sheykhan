<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('academy_content_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('slug');
            $t->unsignedInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true)->index();
            $t->timestamps();
            $t->unique(['academy_id', 'slug']);
        });
        Schema::create('academy_contents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $t->foreignId('category_id')->constrained('academy_content_categories')->cascadeOnDelete();
            $t->string('type', 32)->default('article')->index();
            $t->string('title');
            $t->string('slug');
            $t->string('excerpt', 500)->nullable();
            $t->longText('body')->nullable();
            $t->unsignedInteger('video_duration_seconds')->nullable();
            $t->string('status', 32)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamp('published_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['academy_id', 'slug']);
            $t->index(['academy_id', 'category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_contents');
        Schema::dropIfExists('academy_content_categories');
    }
};
