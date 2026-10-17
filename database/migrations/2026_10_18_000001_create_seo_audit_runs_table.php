<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_audit_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('queued')->index();
            // A unique non-null key guarantees only one site-wide crawl can be active,
            // even when two workers or two web requests race.
            $table->string('active_key', 40)->nullable()->unique();
            $table->string('target_url', 2048);
            $table->unsignedSmallInteger('pages_found')->default(0);
            $table->unsignedSmallInteger('pages_scanned')->default(0);
            $table->unsignedSmallInteger('issues_count')->default(0);
            $table->unsignedSmallInteger('critical_count')->default(0);
            $table->unsignedSmallInteger('warning_count')->default(0);
            $table->unsignedSmallInteger('info_count')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('summary')->nullable();
            $table->json('results')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audit_runs');
    }
};