<?php

use IlluminateDatabaseMigrationsMigration;
use IlluminateDatabaseSchemaBlueprint;
use IlluminateSupportFacadesSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academies', function (Blueprint $table): void {
            $table->dropForeign(['owner_id']);
            $table->dropUnique('academies_owner_id_unique');
            $table->index('owner_id', 'academies_owner_id_index');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('academies', function (Blueprint $table): void {
            $table->dropForeign(['owner_id']);
            $table->dropIndex('academies_owner_id_index');
            $table->unique('owner_id', 'academies_owner_id_unique');
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
