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

        Schema::create('seo_keyword_rankings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seo_keyword_id')->constrained('seo_keywords')->cascadeOnDelete();
            $table->date('observed_on');
            $table->unsignedSmallInteger('position')->nullable();
            $table->unsignedInteger('search_volume')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedInteger('clicks')->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->string('source', 32)->default('manual');
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['seo_keyword_id', 'observed_on']);
            $table->index(['observed_on', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_keyword_rankings');
        Schema::dropIfExists('seo_keywords');
    }
};
