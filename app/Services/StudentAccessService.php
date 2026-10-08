<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Support\Collection;

final class StudentAccessService
{
    public function enrolledCourseIds(User $student): Collection
    {
        if (!$student->hasRole('student')) {
            return collect();
        }

        return $student->enrollments()
            ->active()
            ->whereHas('course', fn ($course) => $course
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()))
            ->where(function ($query): void {
                $query->whereHas('course', fn ($course) => $course->where('access_type', 'free'))
                    ->orWhere(fn ($paid) => $paid->fullyPaid());
            })
            ->whereHas('course.academy', function ($academy) use ($student): void {
                $academy->whereExists(function ($membership) use ($student): void {
                    $membership->selectRaw('1')
                        ->from('academy_user')
                        ->whereColumn('academy_user.academy_id', 'academies.id')
                        ->where('academy_user.user_id', $student->id)
                        ->where('academy_user.status', 'active');
                });
            })
            ->pluck('course_id');
    }

    public function course(User $student, Course $course): bool
    {
        if (!$student->hasRole('student') || !$course->isPublished()) {
            return false;
        }

        if (!$this->academyMember($student, $course->academy_id)) {
            return false;
        }

        if ($course->isFree()) {
            return $student->enrollments()
                ->active()
                ->where('course_id', $course->id)
                ->exists();
        }

        return $student->enrollments()
            ->where('course_id', $course->id)
            ->fullyPaid()
            ->exists();
    }

    public function lesson(User $student, Lesson $lesson): bool
    {
        if (!$student->hasRole('student')) {
            return false;
        }

        $lesson->loadMissing('section.course');
        $course = $lesson->section?->course;

        if (
            !$course
            || !$course->isPublished()
            || $lesson->status !== 'published'
            || ($lesson->published_at && $lesson->published_at->isFuture())
            || !$this->academyMember($student, $course->academy_id)
        ) {
            return false;
        }

        // A published lesson explicitly marked free is the only preview path
        // that can bypass paid enrollment. The course itself must still be
        // published and belong to the student's active academy membership.
        if ($lesson->is_free) {
            return true;
        }

        return $this->course($student, $course);
    }

    public function assignment(User $student, Assignment $assignment): bool
    {
        if (!$student->hasRole('student')) {
            return false;
        }

        $assignment->loadMissing(['course', 'classroom']);

        if ($assignment->status !== 'published' || !$assignment->course || !$this->course($student, $assignment->course)) {
            return false;
        }

        return !$assignment->classroom_id
            || $student->classroomsAsStudent()
                ->whereKey($assignment->classroom_id)
                ->wherePivot('status', 'active')
                ->exists();
    }

    public function exam(User $student, Exam $exam): bool
    {
        if (!$student->hasRole('student')) {
            return false;
        }

        $exam->loadMissing(['course', 'classroom']);

        if ($exam->status !== 'published' || !$exam->course || !$this->course($student, $exam->course)) {
            return false;
        }

        return !$exam->classroom_id
            || $student->classroomsAsStudent()
                ->whereKey($exam->classroom_id)
                ->wherePivot('status', 'active')
                ->exists();
    }

    public function liveClass(User $student, LiveClass $liveClass): bool
    {
        if (!$student->hasRole('student')) {
            return false;
        }

        $liveClass->loadMissing(['course', 'classroom']);

        if (!$liveClass->course || $liveClass->status === 'cancelled') {
            return false;
        }

        if (!$this->course($student, $liveClass->course)) {
            return false;
        }

        return !$liveClass->classroom_id
            || $student->classroomsAsStudent()
                ->whereKey($liveClass->classroom_id)
                ->wherePivot('status', 'active')
                ->exists();
    }

    public function canJoinLiveClass(User $student, LiveClass $liveClass): bool
    {
        if (!$this->liveClass($student, $liveClass) || blank($liveClass->meeting_url) || !$liveClass->scheduled_at) {
            return false;
        }

        $startsAt = $liveClass->scheduled_at;
        $endsAt = $liveClass->scheduled_end_at
            ?? $startsAt->copy()->addMinutes(max(1, (int) $liveClass->duration_minutes));

        if ($liveClass->status === 'live') {
            return now()->lte($endsAt);
        }

        if ($liveClass->status !== 'scheduled') {
            return false;
        }

        return now()->betweenIncluded($startsAt->copy()->subMinutes(15), $endsAt);
    }

    public function attendanceBelongsToStudent(User $student, int $classroomId): bool
    {
        return $student->hasRole('student')
            && $student->classroomsAsStudent()
                ->whereKey($classroomId)
                ->wherePivot('status', 'active')
                ->exists();
    }

    private function academyMember(User $student, int $academyId): bool
    {
        return $student->academies()
            ->whereKey($academyId)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
