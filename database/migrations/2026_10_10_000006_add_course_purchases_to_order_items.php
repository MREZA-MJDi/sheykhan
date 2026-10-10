<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreignId('course_id')->nullable()->after('product_id')->constrained('courses')->restrictOnDelete();
            $table->index(['order_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'course_id']);
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });
    }
};
