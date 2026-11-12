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
        $seedPassword = (string) config('role_access.seed_password', 'password');

        if (blank($seedPassword)) {
            throw new \\RuntimeException('Set SEED_USER_PASSWORD before seeding demo accounts.');
        }

        // Resolve by the stable identity first. Existing demo accounts keep their
        // real mobile number; the seeder must never overwrite another user's number.
        $user = User::withTrashed()->where('email', $email)->first();

        if (! $user) {
            $mobile = $this->availableDemoMobile($mobileKey);

            $user = User::query()->create([
                'email' => $email,
                'name' => $name,
                'mobile' => $mobile,
                'status' => 'active',
                'password' => $seedPassword,
            ]);
        }

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
            throw new \\RuntimeException(
                "Demo account {$email} already belongs to another role; refusing to change its access."
            );
        }

        return $user;
    }

    /**
     * Generate a stable demo mobile number, probing only when another account
     * (including a soft-deleted one) already owns the preferred number.
     */
    private function availableDemoMobile(string $mobileKey): string
    {
        $base = abs(crc32($mobileKey)) % 10000000;

        for ($attempt = 0; $attempt < 10000000; $attempt++) {
            $tail = ($base + $attempt) % 10000000;
            $candidate = '0912' . str_pad((string) $tail, 7, '0', STR_PAD_LEFT);

            if (! User::withTrashed()->where('mobile', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \\RuntimeException('Unable to allocate a unique mobile number for a demo account.');
    }
}
