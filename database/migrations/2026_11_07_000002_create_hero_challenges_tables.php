<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_challenges', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title', 160);
            $table->text('prompt');
            $table->json('sequence')->nullable();
            $table->text('explanation');
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'sort_order', 'id'], 'hero_challenge_rotation_idx');
        });

        Schema::create('hero_challenge_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hero_challenge_id')->constrained()->cascadeOnDelete();
            $table->string('label', 8);
            $table->string('content', 160);
            $table->boolean('is_correct')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['hero_challenge_id', 'label'], 'hero_challenge_option_label_unique');
            $table->index(['hero_challenge_id', 'sort_order'], 'hero_challenge_option_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_challenge_options');
        Schema::dropIfExists('hero_challenges');
    }
};
