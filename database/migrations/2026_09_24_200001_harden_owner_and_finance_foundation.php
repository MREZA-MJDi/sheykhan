<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('academies', function (Blueprint $table): void {
            $table->unique('owner_id', 'academies_owner_id_unique');
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->decimal('price_amount', 14, 2)->default(0)->after('paid_amount');
            $table->string('payment_status', 24)->default('unpaid')->after('status')->index();
            $table->index(['course_id', 'status', 'classroom_id'], 'course_enrollments_owner_lookup');
        });

        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->uuid('key')->unique();
            $table->string('scope', 80);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resource_type')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('request_hash', 64);
            $table->timestamps();

            $table->index(['scope', 'user_id']);
            $table->index(['resource_type', 'resource_id']);
        });

        Schema::create('financial_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('academy_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('course_enrollments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32)->index();
            $table->string('status', 24)->default('completed')->index();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 8)->default('IRT');
            $table->string('reference')->nullable()->unique();
            $table->uuid('idempotency_key')->nullable()->unique('financial_transactions_idempotency_unique');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index(['academy_id', 'status', 'occurred_at'], 'financial_transactions_reporting');
            $table->index(['enrollment_id', 'type', 'status'], 'financial_transactions_enrollment');
        });

        DB::table('course_enrollments')
            ->select(['id', 'course_id', 'student_id', 'paid_amount'])
            ->orderBy('id')
            ->chunkById(500, function ($enrollments): void {
                $courseIds = $enrollments->pluck('course_id')->filter()->unique()->values();
                $prices = DB::table('courses')
                    ->whereIn('id', $courseIds)
                    ->pluck('price', 'id');

                foreach ($enrollments as $enrollment) {
                    $price = (float) ($prices[$enrollment->course_id] ?? 0);
                    $paid = (float) $enrollment->paid_amount;
                    $paymentStatus = $price <= 0
                        ? 'paid'
                        : ($paid >= $price ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));

                    DB::table('course_enrollments')
                        ->where('id', $enrollment->id)
                        ->update([
                            'price_amount' => $price,
                            'payment_status' => $paymentStatus,
                        ]);

                    if ($paid <= 0) {
                        continue;
                    }

                    $academyId = DB::table('courses')
                        ->where('id', $enrollment->course_id)
                        ->value('academy_id');

                    if (!$academyId) {
                        continue;
                    }

                    $createdAt = DB::table('course_enrollments')
                        ->where('id', $enrollment->id)
                        ->value('created_at') ?: now();

                    DB::table('financial_transactions')->updateOrInsert(
                        ['reference' => 'legacy-enrollment-' . $enrollment->id],
                        [
                            'academy_id' => $academyId,
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $enrollment->student_id,
                            'recorded_by' => null,
                            'type' => 'enrollment_payment',
                            'status' => 'completed',
                            'amount' => $paid,
                            'currency' => 'IRT',
                            'idempotency_key' => null,
                            'description' => 'ثبت تراکنش تاریخی ثبت‌نام',
                            'metadata' => json_encode(['source' => 'legacy_enrollment_backfill'], JSON_UNESCAPED_UNICODE),
                            'occurred_at' => $createdAt,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('idempotency_keys');

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropIndex('course_enrollments_owner_lookup');
            $table->dropColumn(['price_amount', 'payment_status']);
        });

        Schema::table('academies', function (Blueprint $table): void {
            $table->dropUnique('academies_owner_id_unique');
        });
    }
};