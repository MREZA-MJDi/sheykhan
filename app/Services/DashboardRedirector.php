<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class DashboardRedirector
{
    public function routeName(User $user): string
    {
        return match (true) {
            $user->hasRole('academy-owner') => 'owner.dashboard',
            $user->hasRole('teacher') => 'teacher.dashboard',
            $user->hasRole('student') => 'student.dashboard',
            $user->hasRole('parent') => 'parent.dashboard',
            default => 'home',
        };
    }

    public function redirect(User $user): RedirectResponse
    {
        $defaultUrl = route($this->routeName($user));

        return redirect()->intended($defaultUrl);
    }
}
