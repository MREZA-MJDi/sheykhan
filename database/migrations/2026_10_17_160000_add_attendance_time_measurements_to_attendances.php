<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->unsignedSmallInteger('scheduled_minutes')->nullable()->after('attendance_date');
            $table->unsignedSmallInteger('attended_minutes')->nullable()->after('scheduled_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['scheduled_minutes', 'attended_minutes']);
        });
    }
};
