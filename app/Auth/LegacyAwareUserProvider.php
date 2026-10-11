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

        if (
            $user instanceof User
            && $user->legacy_source === 'wordpress'
            && is_string($plain)
            && is_string($stored)
            && (str_starts_with($stored, '$P$') || str_starts_with($stored, '$H$'))
        ) {
            if (! app(LegacyWordPressPassword::class)->verify($plain, $stored)) {
                return false;
            }

            // The User model's "hashed" cast converts the legacy password to a
            // current Laravel hash. The next login no longer uses phpass.
            $user->forceFill(['password' => $plain])->save();

            return true;
        }

        return parent::validateCredentials($user, $credentials);
    }
}
