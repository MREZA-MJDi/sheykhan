<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Models\Academy;

final class OwnerPersonResource extends JsonResource
{
    /**
     * A restricted, stable JSON shape for owner member directory rows.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pivot = $this->resource->pivot;
        $joinedAt = $pivot?->joined_at;
        $academy = $request->route('academy');
        $canManageTeachers = $request->user()?->hasPermission('teachers.manage') ?? false;
        $actions = [];

        if ($academy instanceof Academy && $canManageTeachers && $pivot?->role === 'teacher') {
            if ($pivot?->status === 'active') {
                $actions['archive_url'] = route('owner.people.archive-teacher', [$academy, $this->resource]);
            } elseif ($pivot?->status === 'archived') {
                $actions['restore_url'] = route('owner.people.restore-teacher', [$academy, $this->resource]);
            }
        }

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => $this->email,
            'account_status' => $this->status,
            'membership' => [
                'role' => $pivot?->role,
                'status' => $pivot?->status,
                'joined_at' => filled($joinedAt) ? Carbon::parse($joinedAt)->toIso8601String() : null,
            ],
            'actions' => $actions,
        ];
    }
}
