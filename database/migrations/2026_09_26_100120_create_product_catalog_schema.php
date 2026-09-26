<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true)->index();$t->timestamps();});
        Schema::create('products',function(Blueprint $t){
            $t->id();$t->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('title');$t->string('slug')->unique();$t->string('subtitle')->nullable();$t->longText('description')->nullable();$t->string('product_type',32)->index();$t->string('delivery_type',32)->default('download')->index();
            $t->unsignedBigInteger('price')->default(0);$t->unsignedBigInteger('sale_price')->nullable();$t->string('currency',8)->default('IRR');$t->string('status',32)->default('draft')->index();
            $t->boolean('is_featured')->default(false)->index();$t->timestamp('featured_from')->nullable();$t->timestamp('featured_until')->nullable();$t->timestamp('published_at')->nullable()->index();$t->timestamps();
            $t->index(['academy_id','status','is_featured']);
        });
        Schema::create('product_files',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->foreignId('media_id')->constrained()->restrictOnDelete();$t->string('version',32)->default('1.0');$t->boolean('is_primary')->default(false);$t->boolean('is_preview')->default(false);$t->boolean('requires_watermark')->default(false);$t->timestamps();$t->unique(['product_id','media_id']);$t->index(['product_id','is_primary']);});
    }
    public function down(): void { Schema::dropIfExists('product_files'); Schema::dropIfExists('products'); Schema::dropIfExists('product_categories'); }
};