<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\CourseEnrollment;
use App\Models\FinancialTransaction;
use App\Models\IdempotencyKey;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Str;
use Illuminate\Validation\ValidationException;

final class OwnerEnrollmentService
{
    private const IDEMPOTENCY_SCOPE = 'owner.enrollment';

    public function enroll(User $owner, Academy $academy, array $data): CourseEnrollment
    {
        abort_unless($owner->hasRole('academy-owner') && (int) $academy->owner_id === (int) $owner->id, 403);

        $hashPayload = array_merge($data, ['academy_id' => $academy->id]);
        $requestHash = hash(
            'sha256',
            json_encode(
                Arr::sortRecursive($hashPayload),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );

        return DB::transaction(function () use ($owner, $academy, $data, $requestHash): CourseEnrollment {
            DB::table('idempotency_keys')->insertOrIgnore([
                'key' => $data['idempotency_key'],
                'scope' => self::IDEMPOTENCY_SCOPE,
                'user_id' => $owner->id,
                'request_hash' => $requestHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $key = IdempotencyKey::query()
                ->where('key', $data['idempotency_key'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $key->scope !== self::IDEMPOTENCY_SCOPE
                || (int) $key->user_id !== (int) $owner->id
                || !hash_equals($key->request_hash, $requestHash)
            ) {
                throw ValidationException::withMessages([
                    'idempotency_key' => 'این شناسه عملیات قبلاً برای درخواست دیگری استفاده شده است.',
                ]);
            }

            if ($key->resource_id) {
                $existing = CourseEnrollment::query()
                    ->with(['student', 'course', 'classroom'])
                    ->find($key->resource_id);

                if ($existing) {
                    return $existing;
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'نتیجه عملیات قبلی دیگر در دسترس نیست و این درخواست قابل تکرار نیست.',
                ]);
            }

            $course = $academy->courses()
                ->whereKey((int) $data['course_id'])
                ->lockForUpdate()
                ->first();

            if (!$course) {
                throw ValidationException::withMessages([
                    'course_id' => 'دوره انتخاب‌شده متعلق به این آموزشگاه نیست.',
                ]);
            }

            $student = $academy->users()
                ->whereKey((int) $data['student_id'])
                ->wherePivot('role', 'student')
                ->wherePivot('status', 'active')
                ->first();

            if (!$student) {
                throw ValidationException::withMessages([
                    'student_id' => 'دانش‌آموز انتخاب‌شده عضو فعال این آموزشگاه نیست.',
                ]);
            }

            $classroom = null;

            if (!empty($data['classroom_id'])) {
                $classroom = $course->classrooms()
                    ->where('academy_id', $academy->id)
                    ->whereKey((int) $data['classroom_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$classroom) {
                    throw ValidationException::withMessages([
                        'classroom_id' => 'کلاس انتخاب‌شده متعلق به همین دوره نیست.',
                    ]);
                }

                if ($classroom->status !== 'active') {
                    throw ValidationException::withMessages([
                        'classroom_id' => 'این کلاس فعال نیست.',
                    ]);
                }
            }

            $price = (float) $course->price;
            $paidAmount = $course->isFree() ? 0.0 : (float) ($data['paid_amount'] ?? $price);

            if (!$course->isFree() && $paidAmount <= 0) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'برای دوره پولی، مبلغ پرداختی باید بیشتر از صفر باشد.',
                ]);
            }

            if (!$course->isFree() && $paidAmount > $price) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'مبلغ پرداختی نمی‌تواند بیشتر از قیمت دوره باشد.',
                ]);
            }

            $enrollment = CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            $oldPaidAmount = (float) ($enrollment?->paid_amount ?? 0);

            if ($enrollment) {
                $sameCurrentState = $enrollment->status === 'active'
                    && (int) $enrollment->classroom_id === (int) ($classroom?->id)
                    && abs($oldPaidAmount - $paidAmount) < 0.0001;

                if ($sameCurrentState) {
                    $key->update([
                        'resource_type' => CourseEnrollment::class,
                        'resource_id' => $enrollment->id,
                    ]);

                    return $enrollment->fresh(['student', 'course', 'classroom']);
                }

                if ($paidAmount < $oldPaidAmount) {
                    throw ValidationException::withMessages([
                        'paid_amount' => 'مبلغ پرداختی ثبت‌شده قبلی را نمی‌توان بدون ثبت فرآیند مالی کاهش داد.',
                    ]);
                }
            }

            if ($classroom && $classroom->capacity !== null) {
                $alreadyInClass = $classroom->students()
                    ->whereKey($student->id)
                    ->wherePivot('status', 'active')
                    ->exists();

                if (!$alreadyInClass) {
                    $activeStudents = $classroom->students()
                        ->wherePivot('status', 'active')
                        ->count();

                    if ($activeStudents >= $classroom->capacity) {
                        throw ValidationException::withMessages([
                            'classroom_id' => 'ظرفیت این کلاس تکمیل شده است.',
                        ]);
                    }
                }
            }

            if ($enrollment) {
                if ($enrollment->classroom_id && $enrollment->classroom_id !== $classroom?->id) {
                    $oldClassroom = Classroom::query()
                        ->whereKey($enrollment->classroom_id)
                        ->lockForUpdate()
                        ->first();

                    $oldClassroom?->students()->detach($student->id);
                }

                $enrollment->update([
                    'classroom_id' => $classroom?->id,
                    'status' => 'active',
                    'price_amount' => $price,
                    'paid_amount' => $paidAmount,
                    'payment_status' => $this->paymentStatus($price, $paidAmount),
                    'started_at' => $enrollment->started_at ?: now(),
                    'completed_at' => null,
                ]);
            } else {
                $enrollment = CourseEnrollment::create([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'classroom_id' => $classroom?->id,
                    'status' => 'active',
                    'price_amount' => $price,
                    'paid_amount' => $paidAmount,
                    'payment_status' => $this->paymentStatus($price, $paidAmount),
                    'started_at' => now(),
                ]);
            }

            if ($classroom) {
                $classroom->students()->syncWithoutDetaching([
                    $student->id => [
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'completed_at' => null,
                    ],
                ]);
            }

            $difference = $paidAmount - $oldPaidAmount;

            if ($difference > 0.0001) {
                FinancialTransaction::create([
                    'academy_id' => $academy->id,
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $student->id,
                    'recorded_by' => $owner->id,
                    'type' => 'enrollment_payment',
                    'status' => 'completed',
                    'amount' => $difference,
                    'currency' => 'IRT',
                    'reference' => 'enrollment-' . $enrollment->id . '-' . Str::uuid(),
                    'idempotency_key' => $data['idempotency_key'],
                    'description' => 'ثبت پرداخت ثبت‌نام دوره',
                    'metadata' => [
                        'course_id' => $course->id,
                        'source' => 'owner_enrollment',
                    ],
                    'occurred_at' => now(),
                ]);
            }

            $key->update([
                'resource_type' => CourseEnrollment::class,
                'resource_id' => $enrollment->id,
            ]);

            Log::info('Owner enrollment completed', [
                'academy_id' => $academy->id,
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'amount_delta' => $difference,
            ]);

            return $enrollment->fresh(['student', 'course', 'classroom']);
        }, 3);
    }

    private function paymentStatus(float $price, float $paidAmount): string
    {
        if ($price <= 0) {
            return 'paid';
        }

        if ($paidAmount >= $price) {
            return 'paid';
        }

        return $paidAmount > 0 ? 'partial' : 'unpaid';
    }
}
