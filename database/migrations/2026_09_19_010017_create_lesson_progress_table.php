<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->unsignedInteger('seconds_watched')->default(0);
            $table->unsignedInteger('last_position_seconds')->default(0);
            $table->json('watched_segments')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();
            $table->unique(['lesson_id','user_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('lesson_progress'); }
};
