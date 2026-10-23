<?php

namespace App\Services;

use App\Models\LearningResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ParentLearningResourceService
{
    /**
     * Return only active student accounts linked to this parent and to at least
     * one active academy membership. Child IDs from the query string are never
     * trusted without resolving them through this collection.
     *
     * @return Collection<int, User>
     */
    public function children(User $parent): Collection
    {
        return $parent->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', fn (Builder $academies) => $academies
                ->where('academies.status', 'active')
                ->where('academy_user.role', 'student')
                ->where('academy_user.status', 'active'))
            ->with(['academies' => fn ($academies) => $academies
                ->where('academies.status', 'active')
                ->wherePivot('role', 'student')
                ->wherePivot('status', 'active')])
            ->orderBy('users.name')
            ->get();
    }

    public function resolveChild(User $parent, int $childId): ?User
    {
        return $this->children($parent)->firstWhere('id', $childId);
    }

    public function canAccess(User $parent, int $childId, LearningResource $resource): bool
    {
        $child = $this->resolveChild($parent, $childId);

        return $child !== null
            && app(StudentLearningResourceService::class)->canAccess($child, $resource);
    }
}
