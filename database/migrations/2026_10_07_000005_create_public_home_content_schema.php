<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * The public Home content tables are owned by
     * 2026_09_26_100140_create_public_results_schema.php.
     *
     * This migration is intentionally kept as a no-op so the Home feature
     * does not create duplicate achievements/testimonials tables.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
