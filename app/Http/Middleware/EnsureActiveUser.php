<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->status !== 'active' || $user->trashed()) {
            abort(403, 'حساب کاربری شما فعال نیست.');
        }

        return $next($request);
    }
}
