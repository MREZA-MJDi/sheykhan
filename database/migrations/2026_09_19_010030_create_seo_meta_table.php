<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('title')->nullable();
            $table->string('description', 500)->nullable();
            $table->text('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->default('index,follow');
            $table->string('og_title')->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->timestamps();
            $table->unique(['seoable_type','seoable_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('seo_meta'); }
};
