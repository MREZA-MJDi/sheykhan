<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('legal_documents',function(Blueprint $t){$t->id();$t->string('code',64);$t->string('title');$t->string('version',32);$t->longText('content');$t->string('content_hash',64);$t->string('document_type',32)->index();$t->boolean('required_for_purchase')->default(false);$t->boolean('is_active')->default(true)->index();$t->timestamp('published_at')->nullable();$t->timestamps();$t->unique(['code','version']);});
  Schema::create('legal_consents',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->foreignId('document_id')->constrained('legal_documents')->restrictOnDelete();$t->foreignId('order_id')->nullable()->constrained()->nullOnDelete();$t->string('subject_type')->nullable();$t->unsignedBigInteger('subject_id')->nullable();$t->string('document_version',32);$t->string('consent_type',32)->default('accepted')->index();$t->string('content_hash',64);$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->timestamp('accepted_at')->nullable();$t->timestamps();$t->index(['subject_type','subject_id']);$t->index(['user_id','document_id','order_id']);});
 }
 public function down(): void { Schema::dropIfExists('legal_consents'); Schema::dropIfExists('legal_documents'); }
};