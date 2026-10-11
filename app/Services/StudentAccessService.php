<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\ClassSchedule;
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
        return $this->liveClass($student, $liveClass)
            && $this->isLiveClassJoinWindowOpen($liveClass);
    }

    /**
     * Time and link check only. The student index uses already-authorized,
     * relationship-scoped queries, so it can call this without repeating
     * per-row enrollment and classroom membership queries.
     */
    public function isLiveClassJoinWindowOpen(LiveClass $liveClass): bool
    {
        if (
            blank($liveClass->meeting_url)
            || blank($liveClass->provider)
            || ! $liveClass->scheduled_at
            || ! app(MeetingLinkService::class)->urlMatchesProvider(
                (string) $liveClass->provider,
                (string) $liveClass->meeting_url,
            )
        ) {
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

    /**
     * A recurring classroom slot is joinable only with valid course access,
     * active classroom membership, a matching weekday and an open time window.
     */
    public function canJoinClassSchedule(User $student, ClassSchedule $schedule): bool
    {
        if (! $student->hasRole('student')) {
            return false;
        }

        $schedule->loadMissing(['classroom.course']);
        $classroom = $schedule->classroom;
        $course = $classroom?->course;

        if (
            ! $classroom
            || $classroom->status !== 'active'
            || ! $course
            || ! $this->course($student, $course)
            || ! $student->classroomsAsStudent()
                ->whereKey($classroom->id)
                ->wherePivot('status', 'active')
                ->exists()
        ) {
            return false;
        }

        return $this->isClassScheduleJoinWindowOpen($schedule);
    }

    /**
     * Time and URL check only. Student list queries already scope schedules
     * to enrolled courses and active classroom memberships before this call.
     */
    public function isClassScheduleJoinWindowOpen(ClassSchedule $schedule): bool
    {
        if (
            blank($schedule->meeting_url)
            || blank($schedule->provider)
            || (int) $schedule->weekday !== (int) now()->dayOfWeek
            || ! app(MeetingLinkService::class)->urlMatchesProvider(
                (string) $schedule->provider,
                (string) $schedule->meeting_url,
            )
        ) {
            return false;
        }

        $startParts = array_map('intval', explode(':', substr((string) $schedule->start_time, 0, 5)));
        $endParts = array_map('intval', explode(':', substr((string) $schedule->end_time, 0, 5)));

        if (count($startParts) !== 2 || count($endParts) !== 2) {
            return false;
        }

        $startsAt = now()->copy()->startOfDay()->setTime($startParts[0], $startParts[1]);
        $endsAt = now()->copy()->startOfDay()->setTime($endParts[0], $endParts[1]);

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
