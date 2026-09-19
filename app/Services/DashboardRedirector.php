<?php

namespace AppServices;

use AppModelsUser;
use IlluminateHttpRedirectResponse;

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
        $route = $this->routeName($user);

        return route($route) !== null
            ? redirect()->route($route)
            : redirect()->route('home');
    }
}
