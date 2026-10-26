<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

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
        ];
    }
}
