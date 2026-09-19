<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('academy_user', function (Blueprint $table) {
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->index();
            $table->string('status')->default('active')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['academy_id', 'user_id', 'role']);
        });
    }

    public function down(): void { Schema::dropIfExists('academy_user'); }
};
