<?php

namespace App\Policies;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\Media;
use App\Models\User;
use App\Services\CourseAccessService;

class MediaPolicy
{
    public function download(User $user, Media $media): bool
    {
        if (($media->metadata['downloadable'] ?? true) === false) {
            return false;
        }

        return $this->canAccessMedia($user, $media);
    }

    public function stream(User $user, Media $media): bool
    {
        return $this->canAccessMedia($user, $media);
    }

    private function canAccessMedia(User $user, Media $media): bool
    {
        if ($media->visibility === 'public' || $media->uploaded_by === $user->id) {
            return true;
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;

            if ($model instanceof Academy && $this->academyMember($user, $model)) {
                return true;
            }

            if ($model instanceof Course && $this->courseMediaAccess($user, $model, $media)) {
                return true;
            }

            if ($model instanceof Lesson) {
                $model->loadMissing('section.course');

                if (
                    $model->section?->course
                    && $this->lessonMediaAccess($user, $model, $model->section->course, $media)
                ) {
                    return true;
                }
            }

            if ($model instanceof Classroom && $this->classroomAccess($user, $model)) {
                return true;
            }

            if ($model instanceof Assignment) {
                $model->loadMissing(['course', 'classroom']);

                if ($model->classroom && $this->classroomAccess($user, $model->classroom)) {
                    return true;
                }

                if ($model->course && $this->courseAccess($user, $model->course)) {
                    return true;
                }
            }

            if ($model instanceof AssignmentSubmission) {
                $model->loadMissing(['assignment.course', 'assignment.classroom']);

                if (
                    $model->student_id === $user->id
                    || $model->graded_by === $user->id
                    || $model->assignment?->teacher_id === $user->id
                ) {
                    return true;
                }

                if ($model->assignment?->course && $this->courseAccess($user, $model->assignment->course)) {
                    return true;
                }

                if ($model->assignment?->classroom && $this->classroomAccess($user, $model->assignment->classroom)) {
                    return true;
                }
            }

            if ($model instanceof Exam) {
                $model->loadMissing(['course', 'classroom']);

                if ($model->classroom && $this->classroomAccess($user, $model->classroom)) {
                    return true;
                }

                if ($model->course && $this->courseAccess($user, $model->course)) {
                    return true;
                }
            }

            if ($model instanceof ExamAttempt) {
                if (
                    $model->student_id === $user->id
                    || $this->isParentOf($user, $model->student_id)
                ) {
                    return true;
                }
            }

            if ($model instanceof LiveClass) {
                $model->loadMissing(['course', 'classroom']);

                if ($model->classroom && $this->classroomAccess($user, $model->classroom)) {
                    return true;
                }

                if ($model->course && $this->courseAccess($user, $model->course)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function lessonMediaAccess(User $user, Lesson $lesson, Course $course, Media $media): bool
    {
        if (!$this->courseAccessService()->canAccessLesson($user, $lesson)) {
            return false;
        }

        $access = $media->metadata['access'] ?? 'course';

        if ($access === 'paid'
            && !$this->isCourseManager($user, $course)
            && !$this->courseAccessService()->isPaidEnrollment($user, $course)
        ) {
            return false;
        }

        if (
            $access === 'free'
            && !$course->isFree()
            && !$lesson->is_free
            && !$this->isCourseManager($user, $course)
        ) {
            return false;
        }

        return true;
    }

    private function courseMediaAccess(User $user, Course $course, Media $media): bool
    {
        $access = $media->metadata['access'] ?? 'course';

        if ($this->isCourseManager($user, $course)) {
            return true;
        }

        if (!$course->isPublished()) {
            return false;
        }

        if ($access === 'free') {
            if ($user->hasRole('student')) {
                return true;
            }

            return $user->hasRole('parent') && $user->children()->exists();
        }

        if ($access === 'paid') {
            return $this->courseAccessService()->isPaidEnrollment($user, $course);
        }

        return $this->courseAccess($user, $course);
    }

    private function academyMember(User $user, Academy $academy): bool
    {
        return $academy->owner_id === $user->id
            || $user->academies()->whereKey($academy->id)->wherePivot('status', 'active')->exists();
    }

    private function academyOwner(User $user, Academy $academy): bool
    {
        return $academy->owner_id === $user->id
            || $user->academies()
                ->whereKey($academy->id)
                ->wherePivot('role', 'owner')
                ->wherePivot('status', 'active')
                ->exists();
    }

    private function classroomAccess(User $user, Classroom $classroom): bool
    {
        $classroom->loadMissing('academy');

        if ($this->academyOwner($user, $classroom->academy)) {
            return true;
        }

        if ($user->classroomsAsTeacher()->whereKey($classroom->id)->exists()) {
            return true;
        }

        if (
            $user->classroomsAsStudent()
                ->whereKey($classroom->id)
                ->wherePivot('status', 'active')
                ->exists()
        ) {
            return true;
        }

        return $user->children()
            ->with('classroomsAsStudent')
            ->get()
            ->contains(
                fn (User $child) => $child->classroomsAsStudent->contains(
                    fn (Classroom $childClassroom) => $childClassroom->id === $classroom->id
                )
            );
    }

    private function courseAccess(User $user, Course $course): bool
    {
        return $this->courseAccessService()->canAccess($user, $course);
    }

    private function courseAccessService(): CourseAccessService
    {
        return app(CourseAccessService::class);
    }

    private function isCourseManager(User $user, Course $course): bool
    {
        return $course->academy?->owner_id === $user->id
            || $course->teachers()->whereKey($user->id)->exists();
    }

    private function isParentOf(User $user, int $studentId): bool
    {
        return $user->children()->whereKey($studentId)->exists();
    }
}
