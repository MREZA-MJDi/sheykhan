<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_direct_messages', function (Blueprint $table): void {
            $table->foreignId('sender_id')->nullable()->after('teacher_id')->constrained('users')->nullOnDelete();
            $table->string('sender_type', 16)->nullable()->after('sender_id');
            $table->index(['sender_type', 'sender_id', 'created_at'], 'teacher_direct_messages_sender_idx');
        });

        DB::table('teacher_direct_messages')->whereNull('sender_id')->update([
            'sender_id' => DB::raw('teacher_id'),
            'sender_type' => 'teacher',
        ]);
    }

    public function down(): void
    {
        Schema::table('teacher_direct_messages', function (Blueprint $table): void {
            $table->dropIndex('teacher_direct_messages_sender_idx');
            $table->dropForeign(['sender_id']);
            $table->dropColumn(['sender_id', 'sender_type']);
        });
    }
};
