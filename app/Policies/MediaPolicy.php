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

            if ($model instanceof Lesson && $model->relationLoaded('section') && $model->section?->course) {
                if ($this->courseAccess($user, $model->section->course)) {
                    return true;
                }
            }

            if ($model instanceof Classroom && $this->classroomAccess($user, $model)) {
                return true;
            }

            if ($model instanceof Assignment) {
                if ($model->classroom && $this->classroomAccess($user, $model->classroom)) {
                    return true;
                }

                if ($model->course && $this->courseAccess($user, $model->course)) {
                    return true;
                }
            }

            if ($model instanceof AssignmentSubmission) {
                if ($model->student_id === $user->id || $model->graded_by === $user->id) {
                    return true;
                }

                if ($model->assignment?->teacher_id === $user->id) {
                    return true;
                }
            }

            if ($model instanceof Exam) {
                if ($model->classroom && $this->classroomAccess($user, $model->classroom)) {
                    return true;
                }

                if ($model->course && $this->courseAccess($user, $model->course)) {
                    return true;
                }
            }

            if ($model instanceof ExamAttempt && $model->student_id === $user->id) {
                return true;
            }

            if ($model instanceof LiveClass) {
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
            || $user->academies()->whereKey($academy->id)->exists();
    }

    private function academyOwner(User $user, Academy $academy): bool
    {
        return $academy->owner_id === $user->id
            || $user->academies()
                ->whereKey($academy->id)
                ->wherePivot('role', 'owner')
                ->exists();
    }

    private function classroomAccess(User $user, Classroom $classroom): bool
    {
        if ($this->academyOwner($user, $classroom->academy)) {
            return true;
        }

        if ($user->classroomsAsTeacher()->whereKey($classroom->id)->exists()) {
            return true;
        }

        if ($user->classroomsAsStudent()->whereKey($classroom->id)->exists()) {
            return true;
        }

        foreach ($user->children()->with('classroomsAsStudent')->get() as $child) {
            if ($child->classroomsAsStudent->contains('id', $classroom->id)) {
                return true;
            }
        }

        return false;
    }

    private function courseAccess(User $user, Course $course): bool
    {
        if ($this->academyOwner($user, $course->academy)) {
            return true;
        }

        if ($course->teachers()->whereKey($user->id)->exists()) {
            return true;
        }

        if ($user->enrollments()->where('course_id', $course->id)->where('status', 'active')->exists()) {
            return true;
        }

        if ($user->classroomsAsStudent()->where('course_id', $course->id)->exists()) {
            return true;
        }

        foreach ($user->children()->with('classroomsAsStudent')->get() as $child) {
            if ($child->classroomsAsStudent->contains('course_id', $course->id)) {
                return true;
            }
        }

        return false;
    }
}
