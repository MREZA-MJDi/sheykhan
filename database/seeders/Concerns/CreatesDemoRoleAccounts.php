<?php

namespace Database\\Seeders\\Concerns;

use App\\Models\\Role;
use App\\Models\\User;
use Illuminate\\Support\\Facades\\Hash;

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

        // Demo identities are deliberately reset to the documented shared password.
        // No name, mobile, status, profile, or other existing account data is overwritten.
        if (! Hash::check('password', (string) $user->getAuthPassword())) {
            $user->forceFill(['password' => 'password'])->save();
        }

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $hasAnyRole = $user->roles()->exists();

        if (! $hasAnyRole) {
            $user->roles()->attach($role->id);
        } elseif (! $user->roles()->whereKey($role->id)->exists()) {
            throw new \\RuntimeException(
                "Demo account {$email} already belongs to another role; refusing to change its access."
            );
        }

        return $user;
    }
}
