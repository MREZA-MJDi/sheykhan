<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_rights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('rights_holder');
            $table->string('source_type', 32)->index();
            $table->string('license_name', 150);
            $table->string('license_reference', 190)->nullable();
            $table->text('source_url')->nullable();
            $table->string('territory', 120)->default('جهانی');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->json('permitted_uses');
            $table->text('attribution')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('status', 24)->default('draft')->index();
            $table->timestamps();

            $table->index(['media_id', 'status']);
            $table->index(['status', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_rights');
    }
};
