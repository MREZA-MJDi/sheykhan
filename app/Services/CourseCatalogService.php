<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseCatalogService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Course::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with([
                'academy:id,name',
                'teachers:id,name',
                'sections.lessons:id,course_section_id',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->withCount('enrollments')
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublished(Course $course): Course
    {
        $course->load([
            'academy:id,name,slug',
            'teachers:id,name',
            'sections.lessons',
            'media' => fn ($query) => $query
                ->where('visibility', 'public')
                ->orderByPivot('sort_order'),
            'seoMeta',
        ]);

        abort_unless(
            $course->status === 'published'
            && $course->published_at?->isPast()
            && $course->academy?->status === 'active',
            404
        );

        return $course;
    }
}
