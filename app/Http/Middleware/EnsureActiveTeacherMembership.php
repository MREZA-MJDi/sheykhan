<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveTeacherMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isActiveTeacher = $user
            && $user->hasRole('teacher')
            && DB::table('academy_user')
                ->where('user_id', $user->id)
                ->where('role', 'teacher')
                ->where('status', 'active')
                ->exists();

        abort_unless($isActiveTeacher, 403, 'عضویت فعال مدرس برای دسترسی به پنل مدرس لازم است.');

        return $next($request);
    }
}
