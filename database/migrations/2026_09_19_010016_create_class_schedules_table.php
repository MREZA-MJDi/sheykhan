<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->string('meeting_url')->nullable();
            $table->timestamps();
            $table->index(['classroom_id','weekday']);
        });
    }

    public function down(): void { Schema::dropIfExists('class_schedules'); }
};
