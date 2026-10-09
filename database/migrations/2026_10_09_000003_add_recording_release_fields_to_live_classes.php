<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'scheduled_end_at' => fn (Blueprint $table) => $table->timestamp('scheduled_end_at')->nullable(),
            'started_at' => fn (Blueprint $table) => $table->timestamp('started_at')->nullable(),
            'ended_at' => fn (Blueprint $table) => $table->timestamp('ended_at')->nullable(),
            'recording_released_at' => fn (Blueprint $table) => $table->timestamp('recording_released_at')->nullable()->index(),
            'recording_visibility' => fn (Blueprint $table) => $table->string('recording_visibility', 32)->default('enrolled_students'),
        ];

        foreach ($columns as $name => $definition) {
            if (! Schema::hasColumn('live_classes', $name)) {
                Schema::table('live_classes', function (Blueprint $table) use ($definition): void {
                    $definition($table);
                });
            }
        }
    }

    public function down(): void
    {
        $columns = [
            'scheduled_end_at',
            'started_at',
            'ended_at',
            'recording_released_at',
            'recording_visibility',
        ];

        foreach ($columns as $name) {
            if (Schema::hasColumn('live_classes', $name)) {
                Schema::table('live_classes', function (Blueprint $table) use ($name): void {
                    $table->dropColumn($name);
                });
            }
        }
    }
};
