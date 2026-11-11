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

        $seedPassword = (string) config('role_access.seed_password', 'password');

        if (blank($seedPassword)) {
            throw new \RuntimeException('Set SEED_USER_PASSWORD before seeding demo accounts.');
        }

        $user = User::withTrashed()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile' => $mobile,
                'status' => 'active',
                'password' => $seedPassword,
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        // Keep existing identity/profile data intact while syncing the documented
        // demo password with the same value shown by the quick-login panel.
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
