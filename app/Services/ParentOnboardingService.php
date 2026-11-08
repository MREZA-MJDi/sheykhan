<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Support\MobileNumber;
use Illuminate\Validation\ValidationException;

final class ParentOnboardingService
{
    /**
     * Create a parent account when needed and connect it to a student in this academy.
     * A newly-created account receives a one-time temporary password and must change it.
     *
     * @return array{parent: User, created: bool, temporary_password: ?string}
     */
    public function createOrLink(Academy $academy, array $data): array
    {
        return DB::transaction(function () use ($academy, $data): array {
            $student = $academy->students()
                ->where('users.id', (int) $data['student_id'])
                ->where('users.status', 'active')
                ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
                ->lockForUpdate()
                ->firstOrFail();

            $mobile = MobileNumber::normalize($data['mobile'] ?? null);
            if (! MobileNumber::valid($mobile)) {
                throw ValidationException::withMessages(['mobile' => 'شماره موبایل معتبر نیست.']);
            }
            $candidate = User::withTrashed()
                ->where('mobile', $mobile)
                ->lockForUpdate()
                ->first();

            $temporaryPassword = null;
            $created = false;

            if ($candidate) {
                if (! $candidate->hasRole('parent')) {
                    throw ValidationException::withMessages([
                        'mobile' => 'این شماره موبایل قبلاً برای حسابی با نقش دیگری ثبت شده است.',
                    ]);
                }

                if ($candidate->status !== 'active') {
                    throw ValidationException::withMessages([
                        'mobile' => 'حساب والد غیرفعال است؛ پیش از اتصال، وضعیت حساب را بررسی کنید.',
                    ]);
                }

                $parent = $candidate;
            } else {
                $temporaryPassword = Str::password(16, true, true, true, false);
                $parent = User::query()->create([
                    'name' => trim((string) $data['name']),
                    'email' => null,
                    'mobile' => $mobile,
                    'password' => $temporaryPassword,
                    'status' => 'active',
                ]);

                $parentRole = Role::query()->where('slug', 'parent')->firstOrFail();
                $parent->roles()->attach($parentRole->id);
                $parent->forceFill(['must_change_password' => true])->save();
                $created = true;
            }

            ParentProfile::query()->firstOrCreate(
                ['user_id' => $parent->id],
                ['occupation' => 'والد', 'relation_default' => $data['relation']],
            );

            $membership = DB::table('academy_user')
                ->where('academy_id', $academy->id)
                ->where('user_id', $parent->id)
                ->lockForUpdate()
                ->first();

            if ($membership && $membership->role !== 'parent') {
                throw ValidationException::withMessages([
                    'mobile' => 'این حساب در همین آموزشگاه با نقش دیگری عضو است؛ برای جلوگیری از تغییر ناخواستهٔ دسترسی، عضویت جدید ثبت نشد.',
                ]);
            }

            if (! $membership) {
                DB::table('academy_user')->insert([
                    'academy_id' => $academy->id,
                    'user_id' => $parent->id,
                    'role' => 'parent',
                    'status' => 'active',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($membership->status !== 'active') {
                DB::table('academy_user')
                    ->where('academy_id', $academy->id)
                    ->where('user_id', $parent->id)
                    ->where('role', 'parent')
                    ->update(['status' => 'active', 'joined_at' => now(), 'updated_at' => now()]);
            }

            $parent->children()->syncWithoutDetaching([
                $student->id => ['relation' => $data['relation']],
            ]);

            return [
                'parent' => $parent,
                'created' => $created,
                'temporary_password' => $temporaryPassword,
            ];
        });
    }
}
