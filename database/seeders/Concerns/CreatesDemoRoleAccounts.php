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

        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $hasAnyRole = $user->roles()->exists();

        if ($hasAnyRole && ! $user->roles()->whereKey($role->id)->exists()) {
            throw new \RuntimeException(
                "Demo account {$email} already belongs to another role; refusing to change its access."
            );
        }

        $mobileInUse = User::withTrashed()
            ->where('mobile', $mobile)
            ->whereKeyNot($user->getKey())
            ->exists();

        if ($mobileInUse) {
            throw new \RuntimeException(
                "Cannot sync demo account {$email}: mobile {$mobile} is already assigned to another user."
            );
        }

        // These fixed demo identities must always match the credentials shown on
        // the login page, including installations where the account existed before
        // the quick-login configuration was introduced.
        $user->forceFill([
            'name' => $name,
            'mobile' => $mobile,
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
