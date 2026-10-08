<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_banners', function (Blueprint $table): void {
            $table->unsignedTinyInteger('crop_x')->default(50)->after('sort_order');
            $table->unsignedTinyInteger('crop_y')->default(50)->after('crop_x');
        });
    }

    public function down(): void
    {
        Schema::table('home_banners', function (Blueprint $table): void {
            $table->dropColumn(['crop_x', 'crop_y']);
        });
    }
};
