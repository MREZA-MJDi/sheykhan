<?php

namespace Database\Seeders;

use App\Models\CourseEnrollment;
use App\Models\FinancialTransaction;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        CourseEnrollment::query()
            ->with('course:id,academy_id,price')
            ->orderBy('id')
            ->chunkById(200, function ($enrollments): void {
                foreach ($enrollments as $enrollment) {
                    $price = (float) ($enrollment->course?->price ?? 0);
                    $paid = (float) $enrollment->paid_amount;

                    $paymentStatus = $price <= 0
                        ? 'paid'
                        : ($paid >= $price ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'));

                    $enrollment->update([
                        'price_amount' => $price,
                        'payment_status' => $paymentStatus,
                    ]);

                    if ($paid <= 0 || !$enrollment->course?->academy_id) {
                        continue;
                    }

                    FinancialTransaction::updateOrCreate(
                        ['reference' => 'enrollment-payment-' . $enrollment->id],
                        [
                            'academy_id' => $enrollment->course->academy_id,
                            'enrollment_id' => $enrollment->id,
                            'user_id' => $enrollment->student_id,
                            'recorded_by' => null,
                            'type' => 'enrollment_payment',
                            'status' => 'completed',
                            'amount' => $paid,
                            'currency' => 'IRT',
                            'description' => 'پرداخت ثبت‌نام',
                            'metadata' => ['source' => 'initial_seed'],
                            'occurred_at' => $enrollment->created_at,
                        ]
                    );
                }
            });
    }
}
