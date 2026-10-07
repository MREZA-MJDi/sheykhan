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
                $query->where(fn (Builder $scope) => $scope
                    ->whereNotNull('classroom_id')
                    ->whereIn('classroom_id', $classroomIds))
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
            'course:id,academy_id',
            'classroom:id,academy_id,course_id',
            'lesson.section.course',
            'media:id,status',
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
            return $student->classroomsAsStudent()
                ->whereKey($resource->classroom_id)
                ->wherePivot('status', 'active')
                ->exists();
        }

        if ($lessonCourse) {
            return app(StudentAccessService::class)->course($student, $lessonCourse);
        }

        if ($resource->course_id) {
            return app(StudentAccessService::class)->course($student, $resource->course);
        }

        return true;
    }

    private function academyMember(User $student, int $academyId): bool
    {
        return $student->academies()->whereKey($academyId)->wherePivot('status', 'active')->exists();
    }
}
