<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_hero_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedTinyInteger('crop_x')->default(50);
            $table->unsignedTinyInteger('crop_y')->default(46);
            $table->unsignedTinyInteger('mobile_crop_x')->default(54);
            $table->unsignedTinyInteger('mobile_crop_y')->default(50);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_settings');
    }
};
