<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CourseManagementService
{
    public function __construct(private readonly CourseCatalogService $catalog) {}

    public function accessibleAcademies(User $user)
    {
        return Academy::query()
            ->where('status', 'active')
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('users', function ($membership) use ($user): void {
                        $membership->whereKey($user->id)
                            ->where('academy_user.status', 'active')
                            ->where('academy_user.role', 'teacher');
                    });
            })
            ->orderBy('name')
            ->get();
    }

    public function canView(User $user, Course $course): bool
    {
        if (!$user->hasPermission('courses.view')) {
            return false;
        }

        if ($course->academy?->owner_id === $user->id && $user->hasRole('academy-owner')) {
            return true;
        }

        return $course->teachers()->whereKey($user->id)->exists();
    }

    public function canManage(User $user, Course $course): bool
    {
        if (!$user->hasPermission('courses.manage')) {
            return false;
        }

        if ($course->academy?->owner_id === $user->id && $user->hasRole('academy-owner')) {
            return true;
        }

        return $course->teachers()->whereKey($user->id)->exists();
    }

    public function create(User $user, array $data): Course
    {
        abort_unless($user->hasPermission('courses.manage'), 403);

        $academy = $this->resolveAcademy($user, (int) $data['academy_id']);

        return DB::transaction(function () use ($user, $academy, $data): Course {
            $course = $academy->courses()->create([
                'created_by' => $user->id,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($academy, $data['title'], $data['slug'] ?? null),
                'level' => $data['level'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'access_type' => $data['access_type'],
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? 0,
                'price' => $data['access_type'] === 'free' ? 0 : $data['price'],
                'published_at' => ($data['status'] ?? 'draft') === 'published'
                    ? ($data['published_at'] ?? now())
                    : null,
            ]);

            if ($user->hasRole('teacher')) {
                $course->teachers()->syncWithoutDetaching([
                    $user->id => ['is_primary' => true],
                ]);
            }

            $this->catalog->clearPublicCache();

            return $course;
        });
    }

    public function update(User $user, Course $course, array $data): Course
    {
        abort_unless($this->canManage($user, $course), 403);

        $requestedAcademyId = array_key_exists('academy_id', $data)
            ? (int) $data['academy_id']
            : $course->academy_id;

        if ($requestedAcademyId !== (int) $course->academy_id) {
            throw ValidationException::withMessages([
                'academy_id' => 'آموزشگاه یک دوره ساخته‌شده قابل جابه‌جایی نیست.',
            ]);
        }

        $academy = $this->resolveAcademy($user, (int) $course->academy_id);

        return DB::transaction(function () use ($user, $course, $academy, $data): Course {
            $accessType = $data['access_type'] ?? $course->access_type;
            $status = $data['status'] ?? $course->status;

            $course->update([
                'academy_id' => $course->academy_id,
                'title' => $data['title'] ?? $course->title,
                'slug' => blank($data['slug'] ?? null)
                    ? $course->slug
                    : $this->uniqueSlug(
                        $academy,
                        $data['title'] ?? $course->title,
                        $data['slug'],
                        $course->id
                    ),
                'level' => $data['level'] ?? null,
                'status' => $status,
                'access_type' => $accessType,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? 0,
                'price' => $accessType === 'free'
                    ? 0
                    : ($data['price'] ?? $course->price),
                'published_at' => $status === 'published'
                    ? ($data['published_at'] ?? $course->published_at ?? now())
                    : null,
            ]);

            if ($user->hasRole('teacher')) {
                $course->teachers()->syncWithoutDetaching([
                    $user->id => ['is_primary' => true],
                ]);
            }

            $this->catalog->clearPublicCache();

            return $course->refresh();
        });
    }

    public function ownerIndexData(User $owner): array
    {
        $base = Course::query()
            ->whereHas('academy', fn ($query) => $query->where('owner_id', $owner->id));

        $stats = (clone $base)->select([
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published"),
            DB::raw("SUM(CASE WHEN access_type = 'free' THEN 1 ELSE 0 END) as free"),
        ])->first();

        return [
            'courses' => (clone $base)
                ->with('academy:id,name')
                ->withCount([
                    'sections',
                    'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                ])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'stats' => [
                'total' => (int) ($stats->total ?? 0),
                'published' => (int) ($stats->published ?? 0),
                'free' => (int) ($stats->free ?? 0),
            ],
        ];
    }

    public function coursesFor(User $user)
    {
        if ($user->hasRole('academy-owner')) {
            return Course::query()
                ->whereHas('academy', fn ($query) => $query->where('owner_id', $user->id))
                ->withCount('sections')
                ->with('academy:id,name')
                ->latest()
                ->get();
        }

        return $user->taughtCourses()
            ->withCount('sections')
            ->with('academy:id,name')
            ->latest()
            ->get();
    }

    private function uniqueSlug(
        Academy $academy,
        string $title,
        ?string $requestedSlug = null,
        ?int $ignoreCourseId = null,
    ): string {
        $base = Str::slug(Str::transliterate($requestedSlug ?: $title));

        if ($base === '') {
            $base = 'course-' . Str::lower(Str::random(10));
        }

        $slug = $base;
        $suffix = 2;

        while (
            $academy->courses()
                ->when($ignoreCourseId !== null, fn ($query) => $query->where('courses.id', '!=', $ignoreCourseId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function resolveAcademy(User $user, int $academyId): Academy
    {
        $academy = $this->accessibleAcademies($user)->firstWhere('id', $academyId);

        if (!$academy) {
            throw ValidationException::withMessages([
                'academy_id' => 'این آموزشگاه برای حساب شما قابل مدیریت نیست.',
            ]);
        }

        return $academy;
    }
}
