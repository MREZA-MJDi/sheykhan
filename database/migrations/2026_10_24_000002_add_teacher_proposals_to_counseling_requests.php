<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counseling_requests', function (Blueprint $table): void {
            $table->timestamp('proposed_at')->nullable();
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('proposal_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('counseling_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('proposed_by');
            $table->dropColumn(['proposed_at', 'proposal_notes']);
        });
    }
};
