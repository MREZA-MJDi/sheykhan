<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AuditLog;
use App\Models\AcademicGrade;
use App\Models\Role;
use App\Models\StudentIdentityLock;
use App\Models\StudentOnboarding;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\NationalIdLookup;
use App\Support\MobileNumber;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class StudentOnboardingService
{
    private const IDEMPOTENCY_SCOPE = 'owner.student-onboarding';

    public function onboard(User $admin, Academy $academy, array $data): StudentOnboarding
    {
        abort_unless(
            $admin->hasRole('academy-owner')
                && $admin->hasPermission('onboarding.manage')
                && (int) $academy->owner_id === (int) $admin->id,
            403
        );

        $lookup = NationalIdLookup::make((string) $data['national_id']);

        $hashPayload = Arr::except($data, ['national_id']);
        $hashPayload['national_id_lookup'] = $lookup;
        $hashPayload['academy_id'] = $academy->id;

        $requestHash = hash(
            'sha256',
            json_encode(
                Arr::sortRecursive($hashPayload),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );

        $temporaryPassword = null;
        $onboarding = DB::transaction(function () use ($admin, $academy, $data, $lookup, $requestHash, &$temporaryPassword): StudentOnboarding {
            DB::table('idempotency_keys')->insertOrIgnore([
                'key' => $data['idempotency_key'],
                'scope' => self::IDEMPOTENCY_SCOPE,
                'user_id' => $admin->id,
                'request_hash' => $requestHash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $key = DB::table('idempotency_keys')
                ->where('key', $data['idempotency_key'])
                ->lockForUpdate()
                ->first();

            if (
                !$key
                || $key->scope !== self::IDEMPOTENCY_SCOPE
                || (int) $key->user_id !== (int) $admin->id
                || !hash_equals((string) $key->request_hash, $requestHash)
            ) {
                throw ValidationException::withMessages([
                    'idempotency_key' => 'این شناسه عملیات قبلاً برای درخواست دیگری استفاده شده است.',
                ]);
            }

            if ($key->resource_id) {
                $existing = StudentOnboarding::query()
                    ->with(['student', 'requestedGrade'])
                    ->find((int) $key->resource_id);

                if ($existing) {
                    return $existing;
                }

                throw ValidationException::withMessages([
                    'idempotency_key' => 'نتیجه عملیات قبلی دیگر در دسترس نیست و این درخواست قابل تکرار نیست.',
                ]);
            }

            AcademicGrade::query()
                ->where('is_active', true)
                ->whereKey((int) $data['grade_id'])
                ->lockForUpdate()
                ->firstOrFail();

            DB::table('student_identity_locks')->insertOrIgnore([
                'national_id_lookup' => $lookup,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $identityLock = StudentIdentityLock::query()
                ->where('national_id_lookup', $lookup)
                ->lockForUpdate()
                ->firstOrFail();

            $existingProfiles = StudentProfile::query()
                ->where('national_id_lookup', $lookup)
                ->with(['user' => fn ($query) => $query->withTrashed()])
                ->lockForUpdate()
                ->get();

            if ($existingProfiles->count() > 1) {
                throw ValidationException::withMessages([
                    'national_id' => 'برای این کد ملی چند پروفایل تکراری در سامانه پیدا شد؛ ابتدا داده‌های تکراری را بررسی کنید.',
                ]);
            }

            $profile = $existingProfiles->first();
            $student = $profile?->user;

            if ($identityLock->user_id && (!$student || (int) $identityLock->user_id !== (int) $student->id)) {
                $lockedStudent = User::withTrashed()->find((int) $identityLock->user_id);

                if ($lockedStudent && !$student) {
                    $student = $lockedStudent;
                    $profile = $student->studentProfile;
                }
            }

            $normalizedMobile = MobileNumber::normalize($data['mobile'] ?? null);
            if (! MobileNumber::valid($normalizedMobile)) {
                throw ValidationException::withMessages(['mobile' => 'شماره موبایل معتبر نیست.']);
            }

            if ($normalizedMobile) {
                $mobileOwner = User::query()
                    ->where('mobile', $normalizedMobile)
                    ->when($student, fn ($query) => $query->where('users.id', '!=', $student->id))
                    ->first();

                if ($mobileOwner) {
                    throw ValidationException::withMessages([
                        'mobile' => 'این شماره موبایل قبلاً به حساب دیگری متصل شده است.',
                    ]);
                }
            }

            $oldValues = $profile
                ? [
                    'name' => $student?->name,
                    'grade_id' => $profile->grade_id,
                    'school_name' => $profile->school_name,
                    'mobile' => $student?->mobile,
                    'status' => $profile->status,
                ]
                : null;

            if (!$student) {
                $temporaryPassword = Str::password(16, true, true, true, false);
                $student = User::create([
                    'name' => $data['name'],
                    'email' => null,
                    'mobile' => $normalizedMobile,
                    'status' => 'active',
                    'password' => $temporaryPassword,
                ]);
                if (Schema::hasColumn('users', 'must_change_password')) {
                    $student->forceFill(['must_change_password' => true])->save();
                }
            } elseif ($student->trashed()) {
                $student->restore();
            }

            $student->forceFill([
                'name' => $data['name'],
                'mobile' => $normalizedMobile ?: $student->mobile,
                'status' => 'active',
            ])->save();

            $studentRole = Role::query()->where('slug', 'student')->first();

            if (!$studentRole) {
                abort(500, 'نقش دانش‌آموز در سیستم تعریف نشده است.');
            }

            $student->roles()->syncWithoutDetaching([$studentRole->id]);

            $profile = StudentProfile::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'student_number' => $profile?->student_number ?: 'LEGACY-' . strtoupper(substr($lookup, 0, 10)),
                    'grade' => AcademicGrade::query()->whereKey((int) $data['grade_id'])->value('title'),
                    'grade_id' => (int) $data['grade_id'],
                    'school_name' => $data['school_name'] ?? null,
                    'national_id_lookup' => $lookup,
                    'registration_source' => 'legacy',
                    'registered_by' => $admin->id,
                    'onboarded_at' => $profile?->onboarded_at ?: now(),
                    'status' => 'active',
                ]
            );

            $academy->users()->syncWithoutDetaching([
                $student->id => [
                    'role' => 'student',
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);

            $onboarding = StudentOnboarding::query()
                ->where('academy_id', $academy->id)
                ->where('national_id_lookup', $lookup)
                ->lockForUpdate()
                ->first();

            $onboardingData = [
                'academy_id' => $academy->id,
                'admin_id' => $admin->id,
                'student_id' => $student->id,
                'entered_name' => $data['name'],
                'national_id_lookup' => $lookup,
                'requested_grade_id' => (int) $data['grade_id'],
                'school_name' => $data['school_name'] ?? null,
                'mobile' => $normalizedMobile,
                'source' => 'legacy',
                'status' => 'activated',
                'notes' => $data['notes'] ?? null,
                'verified_at' => $onboarding?->verified_at ?: now(),
                'activated_at' => now(),
            ];

            if ($onboarding) {
                $onboarding->update($onboardingData);
            } else {
                $onboarding = StudentOnboarding::create($onboardingData);
            }

            $identityLock->update([
                'user_id' => $student->id,
            ]);

            AuditLog::create([
                'actor_id' => $admin->id,
                'action' => 'legacy_student_onboarded',
                'subject_type' => StudentOnboarding::class,
                'subject_id' => $onboarding->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'academy_id' => $academy->id,
                    'student_id' => $student->id,
                    'grade_id' => (int) $data['grade_id'],
                    'school_name' => $data['school_name'] ?? null,
                    'mobile_present' => (bool) $normalizedMobile,
                    'registration_source' => 'legacy',
                    'status' => 'activated',
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $keyUpdate = [
                'resource_type' => StudentOnboarding::class,
                'resource_id' => $onboarding->id,
                'updated_at' => now(),
            ];

            DB::table('idempotency_keys')
                ->where('key', $data['idempotency_key'])
                ->where('scope', self::IDEMPOTENCY_SCOPE)
                ->update($keyUpdate);

            return $onboarding->fresh(['student.studentProfile', 'requestedGrade']);
        }, 3);

        if ($temporaryPassword) {
            request()->session()->flash('temporary_student_name', $onboarding->student?->name);
            request()->session()->flash('temporary_student_mobile', $onboarding->student?->mobile);
            request()->session()->flash('temporary_student_password', $temporaryPassword);
        }

        return $onboarding;
    }

}
