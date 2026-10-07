<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'mobile')) {
                    $table->string('mobile', 30)->nullable()->index()->after('email');
                }
                if (!Schema::hasColumn('users', 'status')) {
                    $table->string('status', 30)->default('active')->index()->after('password');
                }
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('student_profiles')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('student_profiles', 'grade_id')) {
                    $table->foreignId('grade_id')->nullable()->after('grade')->constrained('academic_grades')->nullOnDelete();
                }
                if (!Schema::hasColumn('student_profiles', 'national_id_lookup')) {
                    $table->string('national_id_lookup', 64)->nullable()->unique()->after('school_name');
                }
                if (!Schema::hasColumn('student_profiles', 'national_id_encrypted')) {
                    $table->longText('national_id_encrypted')->nullable()->after('national_id_lookup');
                }
                if (!Schema::hasColumn('student_profiles', 'registration_source')) {
                    $table->string('registration_source', 40)->default('admin')->index()->after('national_id_encrypted');
                }
                if (!Schema::hasColumn('student_profiles', 'registered_by')) {
                    $table->foreignId('registered_by')->nullable()->after('registration_source')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('student_profiles', 'onboarded_at')) {
                    $table->timestamp('onboarded_at')->nullable()->index()->after('registered_by');
                }
                if (!Schema::hasColumn('student_profiles', 'status')) {
                    $table->string('status', 30)->default('active')->index()->after('onboarded_at');
                }
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 120)->index();
                $table->nullableMorphs('subject');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
                $table->index(['actor_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: this repair migration protects existing data.
    }
};
