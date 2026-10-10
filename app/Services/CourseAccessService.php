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

        if (
            $user->hasAnyRole(['student', 'parent'])
            && !$this->academyMember($user, $course->academy_id)
        ) {
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
        // Purchasing a protected course grants learning access, not file download rights.
        // Neither students nor parents can download course media as a raw file.
        // Parents monitor progress; students consume lessons inside the protected viewer.
        if ($user->hasAnyRole(['student', 'parent'])) {
            return false;
        }

        return $this->canAccess($user, $course);
    }

    public function isPaidEnrollment(User $user, Course $course): bool
    {
        return $user->enrollments()
            ->where('course_id', $course->id)
            ->fullyPaid()
            ->exists();
    }

    private function studentCanAccess(User $user, Course $course): bool
    {
        if ($course->isFree()) {
            return $user->enrollments()
                ->active()
                ->where('course_id', $course->id)
                ->exists();
        }

        return $this->isPaidEnrollment($user, $course);
    }

    private function parentCanAccess(User $user, Course $course): bool
    {
        if ($course->isFree()) {
            // Family access follows a child's actual enrollment in this course;
            // the mere existence of a linked child is not an entitlement.
            return $user->children()
                ->whereHas('enrollments', fn ($query) => $query
                    ->where('course_id', $course->id)
                    ->active())
                ->exists();
        }

        return $user->children()
            ->whereHas('enrollments', fn ($query) => $query
                ->where('course_id', $course->id)
                ->fullyPaid())
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

    private function academyMember(User $user, int $academyId): bool
    {
        return $user->academies()
            ->whereKey($academyId)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
