<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function login(array $credentials, bool $remember = false): User
    {
        $identifier = trim((string) ($credentials['identifier'] ?? ''));
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::query()->whereRaw('LOWER(email) = ?', [Str::lower($identifier)])->first()
            : User::query()->where('mobile', $this->normalizeMobile($identifier))->first();

        if (!$user || $user->status !== 'active' || $user->deleted_at !== null || !Hash::check((string) ($credentials['password'] ?? ''), (string) $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => 'اطلاعات ورود صحیح نیست یا این حساب فعال نیست.',
            ]);
        }

        Auth::login($user, $remember);

        request()->session()->regenerate();

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
