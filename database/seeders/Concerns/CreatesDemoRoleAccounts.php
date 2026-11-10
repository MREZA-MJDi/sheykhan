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
        $generatedMobile = '0912' . str_pad(
            (string) (abs(crc32($mobileKey)) % 10000000),
            7,
            '0',
            STR_PAD_LEFT,
        );

        $user = User::withTrashed()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'mobile' => $generatedMobile,
                'status' => 'active',
                'password' => 'password',
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $hasAnyRole = $user->roles()->exists();

        if ($hasAnyRole && ! $user->roles()->whereKey($role->id)->exists()) {
            throw new \RuntimeException(
                "Demo account {$email} already belongs to another role; refusing to change its access."
            );
        }

        // Existing demo users keep their real mobile number. A generated number is
        // assigned only when the account has no mobile value yet.
        if (blank($user->mobile)) {
            $mobileInUse = User::withTrashed()
                ->where('mobile', $generatedMobile)
                ->whereKeyNot($user->getKey())
                ->exists();

            if ($mobileInUse) {
                throw new \RuntimeException(
                    "Cannot create demo account {$email}: generated mobile {$generatedMobile} is already assigned."
                );
            }

            $user->mobile = $generatedMobile;
        }

        $user->forceFill([
            'name' => $name,
            'status' => 'active',
        ]);

        if (! Hash::check('password', (string) $user->getAuthPassword())) {
            $user->password = 'password';
        }

        $user->save();

        if (! $hasAnyRole) {
            $user->roles()->attach($role->id);
        }

        return $user;
    }
}
