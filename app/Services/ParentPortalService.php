<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ParentPortalService
{
    /**
     * Children that this parent may contact through an active academy membership.
     *
     * @return Collection<int, User>
     */
    public function contactableChildren(User $parent): Collection
    {
        return $parent->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('roles.slug', 'student'))
            ->with(['academies' => fn ($academies) => $academies
                ->where('academies.status', 'active')
                ->wherePivot('role', 'student')
                ->wherePivot('status', 'active')
                ->orderBy('academies.name')])
            ->orderBy('users.name')
            ->get()
            ->filter(fn (User $child) => $child->academies->isNotEmpty())
            ->values();
    }

    /**
     * Resolve a child/academy target from the parent's own relationships.
     * Submitted IDs are never trusted on their own.
     *
     * @return array{0: User, 1: Academy}
     */
    public function resolveTarget(User $parent, int $studentId, int $academyId): array
    {
        $student = $parent->children()
            ->where('users.id', $studentId)
            ->where('users.status', 'active')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('roles.slug', 'student'))
            ->firstOrFail();

        $academy = $student->academies()
            ->where('academies.id', $academyId)
            ->where('academies.status', 'active')
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->firstOrFail();

        return [$student, $academy];
    }
}
