<?php

namespace App\Services;

use App\Models\LearningResource;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class StudentLearningResourceService
{
    public function query(User $student): Builder
    {
        $academyIds = $student->academies()
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        $courseIds = $student->enrollments()
            ->where('status', 'active')
            ->pluck('course_id');

        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->pluck('classrooms.id');

        return LearningResource::query()
            ->with([
                'course:id,academy_id,title',
                'classroom:id,academy_id,title,course_id',
                'lesson:id,title,course_section_id',
                'academy:id',
                'media:id,disk,path,original_name,mime_type,size,status',
            ])
            ->whereIn('academy_id', $academyIds)
            ->where('visibility', 'enrolled_students')
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('media_id')
            ->whereHas('media', fn (Builder $media): Builder => $media->where('status', 'active'))
            ->where(function (Builder $query) use ($courseIds, $classroomIds): void {
                $query->where(function (Builder $scope) use ($classroomIds): void {
                    $scope->whereNotNull('classroom_id')
                        ->whereIn('classroom_id', $classroomIds);
                })->orWhere(function (Builder $scope) use ($courseIds): void {
                    $scope->whereNull('classroom_id')
                        ->whereNotNull('course_id')
                        ->whereIn('course_id', $courseIds);
                })->orWhere(function (Builder $scope) use ($courseIds): void {
                    $scope->whereNull('classroom_id')
                        ->whereNotNull('lesson_id')
                        ->whereHas(
                            'lesson.section',
                            fn (Builder $section) => $section->whereIn('course_id', $courseIds)
                        );
                })->orWhere(function (Builder $scope): void {
                    $scope->whereNull('classroom_id')
                        ->whereNull('course_id')
                        ->whereNull('lesson_id');
                });
            })
            ->where(function (Builder $query): void {
                $query->whereNull('course_id')
                    ->orWhereHas(
                        'course',
                        fn (Builder $course) => $course->whereColumn(
                            'courses.academy_id',
                            'learning_resources.academy_id'
                        )
                    );
            })
            ->where(function (Builder $query): void {
                $query->whereNull('classroom_id')
                    ->orWhereHas(
                        'classroom',
                        fn (Builder $classroom) => $classroom->whereColumn(
                            'classrooms.academy_id',
                            'learning_resources.academy_id'
                        )
                    );
            })
            ->where(function (Builder $query): void {
                $query->whereNull('lesson_id')
                    ->orWhereHas(
                        'lesson.section.course',
                        fn (Builder $course) => $course->whereColumn(
                            'courses.academy_id',
                            'learning_resources.academy_id'
                        )
                    );
            })
            ->where(function (Builder $query): void {
                $query->whereNull('release_at')
                    ->orWhere('release_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function paginate(User $student, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query($student)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function canAccess(User $student, LearningResource $resource): bool
    {
        if (
            !in_array($resource->status, ['active', 'published'], true)
            || $resource->visibility !== 'enrolled_students'
            || ($resource->release_at && $resource->release_at->isFuture())
        ) {
            return false;
        }

        $resource->loadMissing([
            'academy:id',
            'course:id,academy_id',
            'classroom:id,academy_id,course_id',
            'lesson.section.course',
            'media:id,status',
        ]);

        if (!$resource->media || $resource->media->status !== 'active') {
            return false;
        }

        $isAcademyMember = $student->academies()
            ->whereKey($resource->academy_id)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isAcademyMember) {
            return false;
        }

        if ($resource->course && $resource->course->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->classroom && $resource->classroom->academy_id !== $resource->academy_id) {
            return false;
        }

        $lessonCourse = $resource->lesson?->section?->course;

        if (
            $lessonCourse
            && $lessonCourse->academy_id !== $resource->academy_id
        ) {
            return false;
        }

        if (
            $resource->course
            && $lessonCourse
            && $resource->course->id !== $lessonCourse->id
        ) {
            return false;
        }

        if (
            $resource->course
            && $resource->classroom?->course_id
            && $resource->classroom->course_id !== $resource->course->id
        ) {
            return false;
        }

        // A classroom-scoped resource is intentionally narrower than a
        // course-scoped resource. Course enrollment must never bypass it.
        if ($resource->classroom_id) {
            return $student->classroomsAsStudent()
                ->whereKey($resource->classroom_id)
                ->wherePivot('status', 'active')
                ->exists();
        }

        if ($lessonCourse) {
            return $student->enrollments()
                ->where('course_id', $lessonCourse->id)
                ->where('status', 'active')
                ->exists();
        }

        if ($resource->course_id) {
            return $student->enrollments()
                ->where('course_id', $resource->course_id)
                ->where('status', 'active')
                ->exists();
        }

        return true;
    }
}
