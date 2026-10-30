<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protected_file_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_file_id')->constrained('product_files')->cascadeOnDelete();
            $table->foreignId('entitlement_id')->constrained('product_entitlements')->cascadeOnDelete();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('event_type', 24)->default('open');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index(['entitlement_id', 'product_file_id', 'viewed_at'], 'pfv_ent_file_time_idx');
            $table->index(['user_id', 'viewed_at'], 'pfv_user_time_idx');
            $table->index(['order_id', 'viewed_at'], 'pfv_order_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protected_file_views');
    }
};
