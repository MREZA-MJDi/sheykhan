<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\AuditLog;
use App\Support\MobileNumber;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function __construct(
        private readonly LegacyWordPressPassword $legacyPasswords,
    ) {
    }

    public function login(array $credentials, bool $remember = false): User
    {
        $mobile = MobileNumber::normalize((string) ($credentials['mobile'] ?? ''));
        $isConfiguredDemoMobile = collect(config('role_access.test_accounts', []))
            ->contains(fn (array $account): bool => ($account['username'] ?? null) === $mobile);

        $user = (MobileNumber::valid($mobile) || $isConfiguredDemoMobile)
            ? User::query()->where('mobile', $mobile)->first()
            : null;

        $plainPassword = (string) ($credentials['password'] ?? '');
        $isLegacyHash = $user
            && $user->legacy_source === 'wordpress'
            && $this->legacyPasswords->isLegacyHash((string) $user->password);
        $passwordValid = $user
            ? ($isLegacyHash
                ? $this->legacyPasswords->verify($plainPassword, (string) $user->password)
                : Hash::check($plainPassword, (string) $user->password))
            : false;

        if (!$user || $user->status !== 'active' || $user->deleted_at !== null || !$passwordValid) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل یا رمز عبور صحیح نیست، یا این حساب فعال نیست.',
            ]);
        }

        if ($isLegacyHash) {
            $user->forceFill(['password' => Hash::make($plainPassword)])->save();
        }

        Auth::login($user, $remember);
        request()->session()->regenerate();

        try {
            if (Schema::hasTable('audit_logs')) {
                AuditLog::create([
                    'actor_id' => $user->id,
                    'action' => 'user_login',
                    'subject_type' => User::class,
                    'subject_id' => $user->id,
                    'new_values' => ['must_change_password' => (bool) $user->must_change_password],
                    'ip_address' => request()->ip(),
                    'user_agent' => mb_substr((string) request()->userAgent(), 0, 2000),
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $user;
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $roleSlug = in_array($data['account_type'] ?? null, ['student', 'parent'], true)
                ? $data['account_type']
                : 'student';

            $role = Role::query()
                ->where('slug', $roleSlug)
                ->firstOrFail();

            $temporaryPassword = Str::password(16, true, true, true, false);
            $mobile = MobileNumber::normalize((string) $data['mobile']);
            $user = User::create([
                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'email' => null,
                'mobile' => $mobile,
                'status' => 'active',
                'password' => $temporaryPassword,
            ]);
            if (Schema::hasColumn('users', 'must_change_password')) {
                $user->forceFill(['must_change_password' => true])->save();
            }
            request()->session()->flash('registration_mobile', $mobile);
            request()->session()->flash('registration_temporary_password', $temporaryPassword);

            $user->roles()->attach($role->id);

            if ($roleSlug === 'student') {
                StudentProfile::create(['user_id' => $user->id]);
            }

            if ($roleSlug === 'parent') {
                ParentProfile::create(['user_id' => $user->id]);
            }

            Auth::login($user);
            request()->session()->regenerate();

            return $user;
        });
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
