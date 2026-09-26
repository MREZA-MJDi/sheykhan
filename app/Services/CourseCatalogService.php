<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CourseCatalogService
{
    public function paginate(int $perPage = 12): LengthAwarePaginator
    {
        return Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with([
                'academy:id,name',
                'teachers:id,name',
                'sections.lessons:id,course_section_id',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featuredCards(int $limit = 3): array
    {
        return Cache::remember(
            "public:home:courses:{$limit}",
            now()->addMinutes(5),
            fn () => Course::query()
                ->published()
                ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                ->with([
                    'academy:id,name',
                    'teachers:id,name',
                    'sections.lessons:id,course_section_id',
                    'media' => fn ($query) => $query
                        ->where('visibility', 'public')
                        ->orderByPivot('sort_order'),
                ])
                ->latest('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Course $course) => [
                    'title' => $course->title,
                    'description' => $course->short_description ?: $course->description,
                    'category' => $course->academy?->name,
                    'teacher' => $course->teachers->first()?->name,
                    'lessons' => $course->sections->sum(fn ($section) => $section->lessons->count()),
                    'duration' => $this->formatDuration($course->duration_minutes),
                    'price' => $this->formatPrice($course->price, $course->isFree()),
                    'level' => $course->level,
                    'image' => $course->media->first()?->url(),
                    'href' => route('courses.show', $course),
                ])
                ->all()
        );
    }

    public function findPublished(Course $course): Course
    {
        $course->load([
            'academy:id,name,slug,owner_id',
            'teachers:id,name',
            'sections.lessons.media',
            'media' => fn ($query) => $query
                ->where('visibility', 'public')
                ->orderByPivot('sort_order'),
            'seoMeta',
        ]);

        abort_unless(
            $course->isPublished()
            && $course->academy?->status === 'active',
            404
        );

        return $course;
    }

    public function clearPublicCache(): void
    {
        Cache::forget('public:home:courses:3');
        Cache::store('file')->forget('public:home:courses:6');
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'مدت زمان متغیر';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours > 0 && $remaining > 0) {
            return $hours . ' ساعت و ' . $remaining . ' دقیقه';
        }

        return $hours > 0 ? $hours . ' ساعت' : $remaining . ' دقیقه';
    }

    private function formatPrice(float|int|string $price, bool $isFree): string
    {
        if ($isFree) {
            return 'رایگان';
        }

        return number_format((float) $price, 0, '.', ',') . ' تومان';
    }
}
