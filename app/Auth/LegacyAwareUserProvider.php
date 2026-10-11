<?php

namespace App\Auth;

use App\Models\User;
use App\Services\LegacyWordPressPassword;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

final class LegacyAwareUserProvider extends EloquentUserProvider
{
    public function validateCredentials(AuthenticatableContract $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? null;
        $stored = $user->getAuthPassword();
        $legacyPasswords = app(LegacyWordPressPassword::class);

        if (
            $user instanceof User
            && $user->legacy_source === 'wordpress'
            && is_string($plain)
            && is_string($stored)
            && $legacyPasswords->isLegacyHash($stored)
        ) {
            if (! $legacyPasswords->verify($plain, $stored)) {
                return false;
            }

            // The User model's "hashed" cast converts the verified legacy
            // password to Laravel's current hash; next login is standard.
            $user->forceFill(['password' => $plain])->save();

            return true;
        }

        return parent::validateCredentials($user, $credentials);
    }
}
