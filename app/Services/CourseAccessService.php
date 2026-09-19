<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;

final class CourseAccessService
{
    public function canAccess(User $user, Course $course): bool
    {
        $course->loadMissing('academy');

        if ($this->isAcademyOwner($user, $course) || $this->isAssignedTeacher($user, $course)) {
            return true;
        }

        if (!$course->isPublished()) {
            return false;
        }

        if ($user->hasRole('student')) {
            return $this->studentCanAccess($user, $course);
        }

        if ($user->hasRole('parent')) {
            return $this->parentCanAccess($user, $course);
        }

        return false;
    }

    public function canDownload(User $user, Course $course): bool
    {
        return $this->canAccess($user, $course);
    }

    public function canAccessLesson(User $user, \App\Models\Lesson $lesson): bool
    {
        $lesson->loadMissing('section.course');

        $course = $lesson->section?->course;

        if (!$course || !$course->isPublished() || $lesson->status !== 'published') {
            return false;
        }

        if ($this->isAcademyOwner($user, $course) || $this->isAssignedTeacher($user, $course)) {
            return true;
        }

        if ($lesson->is_free) {
            if ($user->hasRole('student')) {
                return true;
            }

            if ($user->hasRole('parent')) {
                return $user->children()->exists();
            }
        }

        return $this->canAccess($user, $course);
    }

    public function isPaidEnrollment(User $user, Course $course): bool
    {
        return $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->where('paid_amount', '>', 0)
            ->exists();
    }

    private function studentCanAccess(User $user, Course $course): bool
    {
        if ($course->isFree()) {
            return true;
        }

        return $this->isPaidEnrollment($user, $course);
    }

    private function parentCanAccess(User $user, Course $course): bool
    {
        if ($course->isFree()) {
            return $user->children()->exists();
        }

        return $user->children()
            ->whereHas('enrollments', fn ($query) => $query
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->where('paid_amount', '>', 0))
            ->exists();
    }

    private function isAcademyOwner(User $user, Course $course): bool
    {
        return $course->academy?->owner_id === $user->id
            || $user->academies()
                ->whereKey($course->academy_id)
                ->wherePivot('role', 'owner')
                ->wherePivot('status', 'active')
                ->exists();
    }

    private function isAssignedTeacher(User $user, Course $course): bool
    {
        return $course->teachers()->whereKey($user->id)->exists();
    }
}
