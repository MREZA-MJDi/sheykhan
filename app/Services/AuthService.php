<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function login(array $credentials, bool $remember = false): User
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'ایمیل یا رمز عبور واردشده صحیح نیست.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        request()->session()->regenerate();

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
