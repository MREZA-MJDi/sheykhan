<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['achievements', 'testimonials'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'publication_consent_at')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->timestamp('publication_consent_at')->nullable();
                });
            }

            if (! Schema::hasColumn($tableName, 'publication_consent_method')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->string('publication_consent_method', 40)->nullable();
                });
            }

            if (! Schema::hasColumn($tableName, 'publication_consent_reference')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->string('publication_consent_reference', 160)->nullable();
                });
            }

            if (! Schema::hasColumn($tableName, 'publication_consent_for_minor')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->boolean('publication_consent_for_minor')->default(false);
                });
            }

            if (! Schema::hasColumn($tableName, 'publication_consent_recorded_by')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->unsignedBigInteger('publication_consent_recorded_by')->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['achievements', 'testimonials'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            $columns = [
                'publication_consent_at',
                'publication_consent_method',
                'publication_consent_reference',
                'publication_consent_for_minor',
                'publication_consent_recorded_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    Schema::table($tableName, function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    });
                }
            }
        }
    }
};
