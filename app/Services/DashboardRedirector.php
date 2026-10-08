<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

final class DashboardRedirector
{
    public function routeName(User $user): string
    {
        $routes = config('role_access.dashboard_routes', []);

        foreach ([
            'academy-owner' => fn (User $u): bool => $u->hasRole('academy-owner'),
            'teacher' => fn (User $u): bool => $u->hasRole('teacher'),
            'student' => fn (User $u): bool => $u->hasRole('student'),
            'parent' => fn (User $u): bool => $u->hasRole('parent'),
        ] as $role => $matches) {
            if ($matches($user) && !empty($routes[$role])) {
                return $routes[$role];
            }
        }

        return 'home';
    }

    public function redirect(User $user): RedirectResponse
    {
        return redirect()->route($this->routeName($user));
    }
}
