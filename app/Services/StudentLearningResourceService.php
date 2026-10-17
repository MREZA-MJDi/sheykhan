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

        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student);

        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->pluck('classrooms.id');

        return LearningResource::query()
            ->with([
                'course:id,academy_id,title,status,published_at,access_type',
                'classroom:id,academy_id,title,course_id,status',
                'classroom.course:id,academy_id,status,published_at,access_type',
                'lesson:id,title,course_section_id',
                'lesson.section:id,course_id',
                'lesson.section.course:id,academy_id,status,published_at,access_type',
                'academy:id',
                'media:id,disk,path,original_name,mime_type,size,status',
            ])
            ->whereIn('academy_id', $academyIds)
            ->where('visibility', 'enrolled_students')
            ->whereIn('status', ['active', 'published'])
            ->whereNotNull('media_id')
            ->whereHas('media', fn (Builder $media): Builder => $media->where('status', 'active'))
            ->where(function (Builder $query) use ($courseIds, $classroomIds): void {
                $query->where(fn (Builder $scope) => $scope
                    ->whereNotNull('classroom_id')
                    ->whereIn('classroom_id', $classroomIds)
                    ->whereHas('classroom.course', fn (Builder $course) => $course
                        ->whereIn('id', $courseIds)
                        ->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())))
                    ->orWhere(fn (Builder $scope) => $scope
                        ->whereNull('classroom_id')
                        ->whereNotNull('course_id')
                        ->whereIn('course_id', $courseIds))
                    ->orWhere(fn (Builder $scope) => $scope
                        ->whereNull('classroom_id')
                        ->whereNotNull('lesson_id')
                        ->whereHas('lesson.section', fn (Builder $section) => $section->whereIn('course_id', $courseIds)))
                    ->orWhere(fn (Builder $scope) => $scope
                        ->whereNull('classroom_id')
                        ->whereNull('course_id')
                        ->whereNull('lesson_id'));
            })
            ->where(fn (Builder $query) => $query
                ->whereNull('course_id')
                ->orWhereHas('course', fn (Builder $course) => $course->whereColumn('courses.academy_id', 'learning_resources.academy_id')))
            ->where(fn (Builder $query) => $query
                ->whereNull('classroom_id')
                ->orWhereHas('classroom', fn (Builder $classroom) => $classroom->whereColumn('classrooms.academy_id', 'learning_resources.academy_id')))
            ->where(fn (Builder $query) => $query
                ->whereNull('lesson_id')
                ->orWhereHas('lesson.section.course', fn (Builder $course) => $course->whereColumn('courses.academy_id', 'learning_resources.academy_id')))
            ->where(fn (Builder $query) => $query->whereNull('release_at')->orWhere('release_at', '<=', now()))
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function paginate(User $student, int $perPage = 12): LengthAwarePaginator
    {
        return $this->query($student)->paginate($perPage)->withQueryString();
    }

    public function canAccess(User $student, LearningResource $resource): bool
    {
        if (
            !$student->hasRole('student')
            || !in_array($resource->status, ['active', 'published'], true)
            || $resource->visibility !== 'enrolled_students'
            || ($resource->release_at && $resource->release_at->isFuture())
        ) {
            return false;
        }

        $resource->loadMissing([
            'academy:id',
            'course:id,academy_id,title,status,published_at,access_type',
            'classroom:id,academy_id,title,course_id,status',
            'classroom.course:id,academy_id,status,published_at,access_type',
            'lesson:id,title,course_section_id',
            'lesson.section:id,course_id',
            'lesson.section.course:id,academy_id,status,published_at,access_type',
            'media:id,disk,path,original_name,mime_type,size,status,visibility',
        ]);

        if (!$resource->media || $resource->media->status !== 'active') {
            return false;
        }

        if (!$this->academyMember($student, $resource->academy_id)) {
            return false;
        }

        if ($resource->course && $resource->course->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->classroom && $resource->classroom->academy_id !== $resource->academy_id) {
            return false;
        }

        $lessonCourse = $resource->lesson?->section?->course;

        if ($lessonCourse && $lessonCourse->academy_id !== $resource->academy_id) {
            return false;
        }

        if ($resource->course && $lessonCourse && $resource->course->id !== $lessonCourse->id) {
            return false;
        }

        if ($resource->course && $resource->classroom?->course_id && $resource->classroom->course_id !== $resource->course->id) {
            return false;
        }

        if ($resource->classroom_id) {
            $classroomCourse = $resource->classroom?->course;

            if (
                !$classroomCourse
                || !$classroomCourse->isPublished()
                || !$student->classroomsAsStudent()
                    ->whereKey($resource->classroom_id)
                    ->wherePivot('status', 'active')
                    ->exists()
            ) {
                return false;
            }

            return app(StudentAccessService::class)->course($student, $classroomCourse);
        }

        if ($lessonCourse) {
            return app(StudentAccessService::class)->course($student, $lessonCourse);
        }

        if ($resource->course_id) {
            return app(StudentAccessService::class)->course($student, $resource->course);
        }

        return true;
    }

    /**
     * Returns null when the student may not access the resource, false when
     * access is allowed but downloading is not, and true only for an explicitly
     * downloadable resource belonging to a free course.
     *
     * Protected/paid content remains view-only even after successful payment.
     */
    public function downloadPermission(User $student, LearningResource $resource): ?bool
    {
        if (! $this->canAccess($student, $resource)) {
            return null;
        }

        if (! $resource->downloadable) {
            return false;
        }

        $resource->loadMissing([
            'course:id,academy_id,title,status,published_at,access_type',
            'classroom:id,academy_id,title,course_id,status',
            'classroom.course:id,academy_id,status,published_at,access_type',
            'lesson:id,title,course_section_id',
            'lesson.section:id,course_id',
            'lesson.section.course:id,academy_id,status,published_at,access_type',
        ]);

        $course = $resource->course
            ?? $resource->classroom?->course
            ?? $resource->lesson?->section?->course;

        // Academy-wide resources without a specific free-course entitlement
        // have no implicit download rights.
        return $course !== null && $course->isFree();
    }

    private function academyMember(User $student, int $academyId): bool
    {
        return $student->academies()->whereKey($academyId)->wherePivot('status', 'active')->exists();
    }
}
