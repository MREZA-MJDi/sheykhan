<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payments', 'proof_media_id')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->foreignId('proof_media_id')->nullable()->after('gateway_response')->constrained('media')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('payments', 'proof_uploaded_at')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->timestamp('proof_uploaded_at')->nullable();
            });
        }

        if (! Schema::hasColumn('payments', 'reviewed_by')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('payments', 'reviewed_at')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->timestamp('reviewed_at')->nullable();
            });
        }

        if (! Schema::hasColumn('payments', 'review_note')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->text('review_note')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'proof_media_id')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('proof_media_id');
            });
        }

        if (Schema::hasColumn('payments', 'reviewed_by')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('reviewed_by');
            });
        }

        foreach (['proof_uploaded_at', 'reviewed_at', 'review_note'] as $column) {
            if (Schema::hasColumn('payments', $column)) {
                Schema::table('payments', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
