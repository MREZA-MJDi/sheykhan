<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_keywords', function (Blueprint $table): void {
            $table->id();
            $table->string('keyword', 255);
            $table->string('keyword_key', 255)->unique();
            $table->string('cluster', 120)->nullable();
            $table->string('intent', 32)->default('informational');
            $table->string('target_url', 2048)->nullable();
            $table->string('priority', 16)->default('medium');
            $table->string('status', 24)->default('idea');
            $table->unsignedInteger('search_volume')->nullable();
            $table->string('volume_source', 32)->nullable();
            $table->unsignedSmallInteger('target_position')->nullable();
            $table->unsignedSmallInteger('last_observed_position')->nullable();
            $table->date('last_observed_at')->nullable();
            $table->string('last_observed_source', 32)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['cluster', 'intent']);
            $table->index(['last_observed_at', 'last_observed_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keywords');
    }
};
