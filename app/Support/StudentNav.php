<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class StudentNav
{
    /**
     * Return the same route-backed navigation items to desktop and mobile.
     * Empty section headings are removed so the menu never renders gaps.
     */
    public static function items(): Collection
    {
        $features = (array) config('student.features', []);
        $items = collect(config('student_nav', []))
            ->filter(fn (array $item): bool => isset($item['group']) || ($features[$item['key']] ?? true))
            ->values();

        return $items->filter(function (array $item, int $index) use ($items): bool {
            if (! isset($item['group'])) {
                return true;
            }

            $next = $items->get($index + 1);

            return $next !== null && ! isset($next['group']);
        })->values();
    }
}
