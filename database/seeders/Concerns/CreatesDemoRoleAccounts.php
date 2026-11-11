<?php

namespace Database\Seeders\Concerns;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesDemoRoleAccounts
{
    protected function createDemoRoleAccount(
        string $email,
        string $name,
        string $mobileKey,
        string $roleSlug,
    ): User {
        $mobile = '0912' . str_pad(
            (string) (abs(crc32($mobileKey)) % 10000000),
            7,
            '0',
            STR_PAD_LEFT,
        );

        $user = User::withTrashed()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile' => $mobile,
                'status' => 'active',
                'password' => 'password',
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        // Seeders and the quick-login panel must always share one configured password.
        // Keep this idempotent and never overwrite profile or account identity fields.
        $seedPassword = (string) config('role_access.seed_password', 'password');

        if (blank($seedPassword)) {
            throw new \\RuntimeException('Set SEED_USER_PASSWORD before seeding demo accounts.');
        }

        if (! Hash::check($seedPassword, (string) $user->getAuthPassword())) {
            $user->forceFill(['password' => $seedPassword])->save();
        }

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $hasAnyRole = $user->roles()->exists();

        if (! $hasAnyRole) {
            $user->roles()->attach($role->id);
        } elseif (! $user->roles()->whereKey($role->id)->exists()) {
            throw new \RuntimeException(
                "Demo account {$email} already belongs to another role; refusing to change its access."
            );
        }

        return $user;
    }
}
