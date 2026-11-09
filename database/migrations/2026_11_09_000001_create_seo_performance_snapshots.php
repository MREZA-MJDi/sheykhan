<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_performance_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('dimension', 12);
            $table->char('page_hash', 64);
            $table->text('page_url');
            $table->char('query_hash', 64);
            $table->string('query', 1024)->nullable();
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->decimal('ctr', 12, 10)->nullable();
            $table->decimal('position', 12, 6)->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->unique(
                ['date', 'dimension', 'page_hash', 'query_hash'],
                'seo_performance_daily_unique'
            );
            $table->index(['dimension', 'date'], 'seo_performance_dimension_date');
            $table->index(['page_hash', 'date'], 'seo_performance_page_date');
            $table->index(['query_hash', 'date'], 'seo_performance_query_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_performance_snapshots');
    }
};
