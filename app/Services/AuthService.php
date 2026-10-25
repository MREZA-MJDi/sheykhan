<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\AuditLog;
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
        $identifier = trim((string) ($credentials['identifier'] ?? ''));
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::query()->whereRaw('LOWER(email) = ?', [Str::lower($identifier)])->first()
            : User::query()->where('mobile', $this->normalizeMobile($identifier))->first();

        // The verified legacy export contains students without email/mobile.
        // Their original WordPress numeric ID is an alternate identifier, never
        // a substitute for the password check.
        if (!$user && ctype_digit($identifier)) {
            $user = User::query()
                ->where('legacy_source', 'wordpress')
                ->where('legacy_id', (int) $identifier)
                ->first();
        }

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
                'identifier' => 'اطلاعات ورود صحیح نیست یا این حساب فعال نیست.',
            ]);
        }

        // Upgrade a verified WordPress hash to the configured Laravel hasher
        // immediately, so the legacy verifier is needed only for first login.
        if ($isLegacyHash) {
            $user->forceFill(['password' => Hash::make($plainPassword)])->save();
        }

        Auth::login($user, $remember);

        request()->session()->regenerate();

        // Store a lightweight audit event; credentials and login identifiers are never logged.
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

    private function normalizeMobile(string $value): string
    {
        $digits = strtr($value, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9']);
        $digits = preg_replace('/\D+/', '', $digits) ?? '';

        if (str_starts_with($digits, '0098')) {
            return '0' . substr($digits, 4);
        }

        if (str_starts_with($digits, '98') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        return $digits;
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

            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => $data['password'],
            ]);

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
