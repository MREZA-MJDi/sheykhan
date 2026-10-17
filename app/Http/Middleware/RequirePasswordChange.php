<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        // A forced-password-change user must still be able to complete the
        // change or securely sign out. All other web routes are gated.
        if ($request->routeIs('password.change.show', 'password.change.update', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'برای ادامه، ابتدا رمز عبور موقت خود را تغییر دهید.',
                'password_change_required' => true,
            ], 423);
        }

        return redirect()->route('password.change.show')
            ->with('warning', 'برای امنیت حساب، پیش از ادامه باید رمز عبور خود را تغییر دهید.');
    }
}
