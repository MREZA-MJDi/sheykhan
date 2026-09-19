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
        if ($media->visibility === 'public' || $media->uploaded_by === $user->id) {
            return true;
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;

            if ($model instanceof Academy && $this->academyMember($user, $model)) {
                return true;
            }

            if ($model instanceof Course && $this->courseAccess($user, $model)) {
                return true;
            }

            if ($model instanceof Lesson) {
                $model->loadMissing('section.course');

                if ($model->section?->course && $this->courseAccess($user, $model->section->course)) {
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
        return app(CourseAccessService::class)->canAccess($user, $course);
    }

    private function isParentOf(User $user, int $studentId): bool
    {
        return $user->children()->whereKey($studentId)->exists();
    }
}
