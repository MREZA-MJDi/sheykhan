<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademicGrade;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class StudentAccountService
{
    /** Create account, profile and academy membership atomically. The password is never audited. */
    public function create(User $admin, Academy $academy, array $data): array
    {
        abort_unless(
            $admin->hasRole('academy-owner')
                && $admin->hasPermission('onboarding.manage')
                && (int) $academy->owner_id === (int) $admin->id,
            403,
        );

        return DB::transaction(function () use ($admin, $academy, $data): array {
            $grade = AcademicGrade::query()->where('is_active', true)
                ->whereKey((int) $data['grade_id'])->lockForUpdate()->first();

            if (! $grade) {
                throw ValidationException::withMessages([
                    'grade_id' => 'این پایه دیگر فعال نیست؛ صفحه را تازه کن و پایه‌ای معتبر انتخاب کن.',
                ]);
            }

            $email = strtolower(trim((string) $data['email']));
            if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                throw ValidationException::withMessages(['email' => 'این ایمیل به یک حساب دیگر اختصاص پیدا کرده است.']);
            }

            $mobile = $this->normalizeMobile($data['mobile'] ?? null);
            if ($mobile && User::query()->where('mobile', $mobile)->exists()) {
                throw ValidationException::withMessages(['mobile' => 'این شماره موبایل به یک حساب دیگر متصل است.']);
            }

            $studentRole = Role::query()->where('slug', 'student')->first();
            if (! $studentRole) abort(500, 'نقش دانش‌آموز در سیستم تعریف نشده است.');

            $temporaryPassword = Str::password(16, true, true, true, false);
            $student = User::query()->create([
                'name' => trim((string) $data['name']),
                'email' => $email,
                'mobile' => $mobile,
                'status' => 'active',
                'password' => Hash::make($temporaryPassword),
            ]);

            if (Schema::hasColumn('users', 'must_change_password')) {
                $student->forceFill(['must_change_password' => true])->save();
            }

            $student->roles()->syncWithoutDetaching([$studentRole->id]);

            $studentNumber = $this->studentNumber();
            $profileData = [
                'user_id' => $student->id,
                'student_number' => $studentNumber,
                'birth_date' => $data['birth_date'] ?? null,
                'grade' => $grade->title,
                'school_name' => filled($data['school_name'] ?? null) ? trim((string) $data['school_name']) : null,
            ];
            foreach ([
                'grade_id' => (int) $grade->id,
                'registration_source' => 'admin',
                'registered_by' => $admin->id,
                'onboarded_at' => now(),
                'status' => 'active',
            ] as $field => $value) {
                if (Schema::hasColumn('student_profiles', $field)) $profileData[$field] = $value;
            }
            $profile = StudentProfile::query()->create($profileData);

            $academy->users()->syncWithoutDetaching([
                $student->id => ['role' => 'student', 'status' => 'active', 'joined_at' => now()],
            ]);

            if (Schema::hasTable('audit_logs')) {
                AuditLog::query()->create([
                    'actor_id' => $admin->id,
                    'action' => 'student_account_created',
                    'subject_type' => User::class,
                    'subject_id' => $student->id,
                    'new_values' => [
                        'academy_id' => $academy->id,
                        'student_id' => $student->id,
                        'grade_id' => $grade->id,
                        'student_number' => $studentNumber,
                        'mobile_present' => (bool) $mobile,
                        'must_change_password' => Schema::hasColumn('users', 'must_change_password'),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => mb_substr((string) request()->userAgent(), 0, 2000),
                ]);
            }

            return ['student' => $student->setRelation('studentProfile', $profile), 'temporary_password' => $temporaryPassword];
        }, 3);
    }

    private function studentNumber(): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = 'STU-' . strtoupper(Str::random(10));
            if (! StudentProfile::query()->where('student_number', $candidate)->exists()) return $candidate;
        }
        throw new \RuntimeException('Could not generate a unique student number.');
    }

    private function normalizeMobile(?string $value): ?string
    {
        if (blank($value)) return null;
        $digits = strtr((string) $value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        $digits = preg_replace('/\D+/', '', $digits) ?? '';
        if (str_starts_with($digits, '0098')) return '0' . substr($digits, 4);
        if (str_starts_with($digits, '98') && strlen($digits) === 12) return '0' . substr($digits, 2);
        return $digits !== '' ? $digits : null;
    }
}
