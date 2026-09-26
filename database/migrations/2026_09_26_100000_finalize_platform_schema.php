<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('academic_grades', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('title', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->string('email')->nullable()->change();
            $table->unique('email');
            $table->string('mobile', 32)->nullable()->index();
            $table->string('status', 32)->default('active')->index();
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $table->string('national_id_lookup', 64)->nullable()->unique();
            $table->text('national_id_encrypted')->nullable();
            $table->string('registration_source', 32)->default('self')->index();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('onboarded_at')->nullable();
            $table->string('status', 32)->default('active')->index();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->index(['academy_id', 'grade_id', 'academic_year_id']);
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registration_source', 32)->default('self')->index();
            $table->index(['student_id', 'academic_year_id', 'status']);
        });

        Schema::table('live_classes', function (Blueprint $table) {
            $table->timestamp('scheduled_end_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('recording_released_at')->nullable();
            $table->string('recording_visibility', 32)->default('enrolled')->index();
        });

        Schema::create('course_grade', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained('academic_grades')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['course_id', 'grade_id']);
        });

        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('resource_type', 32)->default('document')->index();
            $table->string('visibility', 32)->default('enrolled_students')->index();
            $table->timestamp('release_at')->nullable()->index();
            $table->boolean('downloadable')->default(true);
            $table->string('status', 32)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['academy_id', 'course_id', 'classroom_id', 'status']);
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->string('product_type', 32)->index();
            $table->string('delivery_type', 32)->default('download')->index();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('sale_price')->nullable();
            $table->string('currency', 8)->default('IRR');
            $table->string('status', 32)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('featured_from')->nullable();
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'is_featured']);
        });

        Schema::create('product_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained()->restrictOnDelete();
            $table->string('version', 32)->default('1.0');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_preview')->default(false);
            $table->boolean('requires_watermark')->default(false);
            $table->timestamps();
            $table->unique(['product_id', 'media_id']);
            $table->index(['product_id', 'is_primary']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 64)->unique();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->string('currency', 8)->default('IRR');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->string('billing_name')->nullable();
            $table->string('billing_mobile', 32)->nullable();
            $table->boolean('legal_consent_completed')->default(false);
            $table->timestamp('paid_at')->nullable()->index();
            $table->timestamps();
            $table->index(['buyer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('beneficiary_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('product_title_snapshot');
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->timestamps();
            $table->index(['order_id', 'product_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 64);
            $table->string('authority', 128)->nullable()->index();
            $table->string('transaction_id', 128)->nullable()->index();
            $table->string('tracking_code', 128)->nullable();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('currency', 8)->default('IRR');
            $table->string('status', 32)->default('pending')->index();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });

        Schema::create('product_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 32)->default('active')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'product_id', 'status']);
        });

        Schema::create('product_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entitlement_id')->constrained('product_entitlements')->cascadeOnDelete();
            $table->foreignId('product_file_id')->constrained()->restrictOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('downloaded_at')->useCurrent();
            $table->index(['entitlement_id', 'product_file_id']);
        });

        Schema::create('protected_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_file_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entitlement_id')->constrained('product_entitlements')->cascadeOnDelete();
            $table->string('source_checksum', 64)->nullable();
            $table->string('generated_path');
            $table->string('watermark_text')->nullable();
            $table->string('watermark_type', 32)->default('text');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 32)->default('processing')->index();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();
            $table->unique(['product_file_id', 'entitlement_id']);
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->string('title');
            $table->string('version', 32);
            $table->longText('content');
            $table->string('content_hash', 64);
            $table->string('document_type', 32)->index();
            $table->boolean('required_for_purchase')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['code', 'version']);
        });

        Schema::create('legal_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('legal_documents')->restrictOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('document_version', 32);
            $table->string('consent_type', 32)->default('accepted')->index();
            $table->string('content_hash', 64);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'document_id', 'order_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('display_name');
            $table->string('achievement_type', 32)->index();
            $table->string('school_name')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('media_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'is_featured']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name');
            $table->string('role', 32)->default('parent')->index();
            $table->text('content_text')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'is_featured']);
        });

        Schema::create('academy_content_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['academy_id', 'slug']);
        });

        Schema::create('academy_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained('academy_content_categories')->cascadeOnDelete();
            $table->string('type', 32)->default('article')->index();
            $table->string('title');
            $table->string('slug');
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->unsignedInteger('video_duration_seconds')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['academy_id', 'slug']);
            $table->index(['academy_id', 'category_id', 'status']);
        });

        Schema::create('student_onboardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entered_name');
            $table->string('national_id_lookup', 64)->index();
            $table->foreignId('requested_grade_id')->nullable()->constrained('academic_grades')->nullOnDelete();
            $table->string('school_name')->nullable();
            $table->string('mobile', 32)->nullable();
            $table->string('source', 32)->default('legacy')->index();
            $table->string('status', 32)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->index(['academy_id', 'status', 'national_id_lookup']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['actor_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('student_onboardings');
        Schema::dropIfExists('academy_contents');
        Schema::dropIfExists('academy_content_categories');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('legal_consents');
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('protected_files');
        Schema::dropIfExists('product_downloads');
        Schema::dropIfExists('product_entitlements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('product_files');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('learning_resources');
        Schema::dropIfExists('course_grade');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('academic_grades');

        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_end_at','started_at','ended_at','recording_released_at','recording_visibility'
            ]);
        });
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['academic_year_id','registered_by','registration_source']);
        });
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['grade_id','academic_year_id']);
        });
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'grade_id','national_id_lookup','national_id_encrypted','registration_source',
                'registered_by','onboarded_at','status'
            ]);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
            $table->dropIndex(['mobile']);
            $table->dropIndex(['status']);
            $table->dropColumn(['mobile','status']);
        });
    }
};
