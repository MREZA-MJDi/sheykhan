<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Support\PersianUi;

class CourseCatalogService
{
    public function paginate(int $perPage = 12, ?int $gradeId = null): LengthAwarePaginator
    {
        $query = Course::query()
            ->published()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->when($gradeId, fn ($query) => $query->whereHas('grades', fn ($grades) => $grades->whereKey($gradeId)))
            ->with([
                'academy:id,name',
                'teachers:id,name',
                'sections.lessons' => fn ($query) => $query
                    ->select('id', 'course_section_id', 'title', 'is_free', 'sort_order')
                    ->orderBy('sort_order'),
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->orderByPivot('sort_order'),
            ])
            ->latest('published_at');

        return $query
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
                    'grades:id,title',
                    'sections.lessons' => fn ($query) => $query
                        ->select('id', 'course_section_id', 'title', 'is_free', 'sort_order')
                        ->orderBy('sort_order'),
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
                    'lessons' => PersianUi::digits($course->sections->sum(fn ($section) => $section->lessons->count())),
                    'previewLessons' => $course->sections
                        ->flatMap(fn ($section) => $section->lessons)
                        ->take(3)
                        ->map(fn ($lesson) => [
                            'title' => $lesson->title,
                            'is_free' => (bool) $lesson->is_free,
                        ])
                        ->values()
                        ->all(),
                    'grades' => $course->grades->pluck('title')->values()->all(),
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
            'sections.lessons' => fn ($query) => $query
                ->where('status', 'published')
                ->where(fn ($query) => $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now())
                )
                ->select([
                    'id',
                    'course_section_id',
                    'title',
                    'slug',
                    'type',
                    'summary',
                    'duration_seconds',
                    'is_free',
                    'status',
                    'published_at',
                    'sort_order',
                ])
                ->orderBy('sort_order'),
            'grades:id,title',
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
            return PersianUi::digits($hours) . ' ساعت و ' . PersianUi::digits($remaining) . ' دقیقه';
        }

        return $hours > 0 ? PersianUi::digits($hours) . ' ساعت' : PersianUi::digits($remaining) . ' دقیقه';
    }

    private function formatPrice(float|int|string $price, bool $isFree): string
    {
        if ($isFree) {
            return 'رایگان';
        }

        return PersianUi::money($price);
    }
}
