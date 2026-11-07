<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

final class GuideController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $roleMap = [
            'owner' => ['role' => 'academy-owner', 'title' => 'مدیر آموزشگاه / ادمین', 'dashboard' => 'owner.dashboard'],
            'teacher' => ['role' => 'teacher', 'title' => 'مدرس', 'dashboard' => 'teacher.dashboard'],
            'parent' => ['role' => 'parent', 'title' => 'والد', 'dashboard' => 'parent.dashboard'],
            'student' => ['role' => 'student', 'title' => 'دانش‌آموز', 'dashboard' => 'student.dashboard'],
            'seo' => ['role' => 'seo-specialist', 'title' => 'متخصص سایت و محتوا', 'dashboard' => 'owner.seo.index'],
        ];

        $availableRoles = collect($roleMap)
            ->filter(fn (array $meta): bool => $user->hasRole($meta['role']))
            ->keys()
            ->values();

        abort_if($availableRoles->isEmpty(), 403);

        $requestedRole = (string) $request->query('role', '');
        $role = $availableRoles->contains($requestedRole)
            ? $requestedRole
            : $availableRoles->first();

        $meta = $roleMap[$role];

        return view('guides.show', [
            'role' => $role,
            'roleTitle' => $meta['title'],
            'dashboardRoute' => $meta['dashboard'],
            'availableRoles' => $availableRoles,
            'roleMap' => $roleMap,
        ]);
    }
}
