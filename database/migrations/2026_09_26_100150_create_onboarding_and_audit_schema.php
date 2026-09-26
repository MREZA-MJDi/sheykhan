<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_onboardings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $t->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('entered_name');
            $t->string('national_id_lookup', 64);
            $t->foreignId('requested_grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $t->string('school_name')->nullable();
            $t->string('mobile', 32)->nullable();
            $t->string('source', 32)->default('legacy')->index();
            $t->string('status', 32)->default('pending')->index();
            $t->text('notes')->nullable();
            $t->timestamp('verified_at')->nullable();
            $t->timestamp('activated_at')->nullable();
            $t->timestamps();
            $t->unique(['academy_id', 'national_id_lookup']);
            $t->index(['academy_id', 'status']);
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action', 64)->index();
            $t->string('subject_type')->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->timestamps();
            $t->index(['subject_type', 'subject_id']);
            $t->index(['actor_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('student_onboardings');
    }
};
