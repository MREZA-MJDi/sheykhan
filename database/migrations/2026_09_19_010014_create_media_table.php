<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('extension', 32)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 64)->nullable()->index();
            $table->string('visibility')->default('private')->index();
            $table->string('collection')->default('default')->index();
            $table->json('metadata')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['disk','path']);
        });
    }

    public function down(): void { Schema::dropIfExists('media'); }
};
