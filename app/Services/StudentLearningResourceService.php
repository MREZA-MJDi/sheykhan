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
            ->whereIn('status', ['active', 'published'])
            ->pluck('course_id');

        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->pluck('classrooms.id');

        return LearningResource::query()
            ->with([
                'course:id,title',
                'classroom:id,title',
                'lesson:id,title,course_section_id',
                'academy:id',
                'media:id,original_name,mime_type,size,status',
            ])
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->whereNull('release_at')
                    ->orWhere('release_at', '<=', now());
            })
            ->where(function (Builder $query) use ($courseIds, $classroomIds): void {
                $query->where(function (Builder $scope) use ($courseIds): void {
                    $scope->whereIn('course_id', $courseIds);
                })->orWhere(function (Builder $scope) use ($classroomIds): void {
                    $scope->whereIn('classroom_id', $classroomIds);
                })->orWhere(function (Builder $scope) use ($courseIds): void {
                    $scope->whereNotNull('lesson_id')
                        ->whereHas('lesson.section', fn (Builder $section) => $section->whereIn('course_id', $courseIds));
                })->orWhere(function (Builder $scope) {
                    $scope->whereNull('course_id')
                        ->whereNull('classroom_id')
                        ->whereNull('lesson_id');
                });
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
            || ($resource->release_at && $resource->release_at->isFuture())
        ) {
            return false;
        }

        $resource->loadMissing(['academy:id', 'lesson.section.course', 'classroom', 'course']);

        if (!$student->academies()
            ->whereKey($resource->academy_id)
            ->wherePivot('status', 'active')
            ->exists()) {
            return false;
        }

        if ($resource->course && $resource->course->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->classroom && $resource->classroom->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->lesson?->section?->course && $resource->lesson->section->course->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->course_id && $student->enrollments()
            ->where('course_id', $resource->course_id)
            ->where('status', 'active')
            ->exists()) {
            return true;
        }

        if ($resource->classroom_id && $student->classroomsAsStudent()
            ->whereKey($resource->classroom_id)
            ->wherePivot('status', 'active')
            ->exists()) {
            return true;
        }

        if ($resource->lesson?->section?->course_id && $student->enrollments()
            ->where('course_id', $resource->lesson->section->course_id)
            ->where('status', 'active')
            ->exists()) {
            return true;
        }

        return $resource->course_id === null
            && $resource->classroom_id === null
            && $resource->lesson_id === null
            && $student->academies()
                ->whereKey($resource->academy_id)
                ->wherePivot('status', 'active')
                ->exists();
    }
}
