<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('level')->nullable();
            $table->string('status')->default('draft')->index();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['academy_id', 'slug']);
            $table->index(['academy_id','status']);
        });
    }

    public function down(): void { Schema::dropIfExists('courses'); }
};
