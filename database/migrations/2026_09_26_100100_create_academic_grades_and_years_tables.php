<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('academic_grades', function (Blueprint $table) {
            $table->id(); $table->string('code',32)->unique(); $table->string('title',120);
            $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id(); $table->string('title',50)->unique(); $table->date('start_date')->nullable(); $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false)->index(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('academic_years'); Schema::dropIfExists('academic_grades'); }
};