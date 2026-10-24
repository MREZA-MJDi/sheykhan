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
                        ->where('academy_user.role', 'student')
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

        return $this->studentBelongsToTeacherGroup(
            $student,
            (int) $assignment->course_id,
            (int) $assignment->teacher_id,
            $assignment->classroom_id === null ? null : (int) $assignment->classroom_id,
        );
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

        return $this->studentBelongsToTeacherGroup(
            $student,
            (int) $exam->course_id,
            (int) $exam->teacher_id,
            $exam->classroom_id === null ? null : (int) $exam->classroom_id,
        );
    }

    /**
     * Historical results remain available after an exam closes, but only to a
     * student who had access to the same course/classroom and submitted an attempt.
     */
    public function canViewExamResult(User $student, Exam $exam): bool
    {
        if (! $student->hasRole('student')) {
            return false;
        }

        $exam->loadMissing(['course', 'classroom']);

        if (! $exam->course || ! $this->course($student, $exam->course)) {
            return false;
        }

        if (! $this->studentBelongsToTeacherGroup(
            $student,
            (int) $exam->course_id,
            (int) $exam->teacher_id,
            $exam->classroom_id === null ? null : (int) $exam->classroom_id,
        )) {
            return false;
        }

        return $exam->attempts()
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
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

        return $this->studentBelongsToTeacherGroup(
            $student,
            (int) $liveClass->course_id,
            (int) $liveClass->teacher_id,
            $liveClass->classroom_id === null ? null : (int) $liveClass->classroom_id,
        );
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

    /**
     * Teacher-authored assessments and sessions belong to the teacher's own
     * active classroom group, not to every student enrolled in the same course.
     * A classroom-less item means "all of this teacher's groups for this course",
     * never "all course students regardless of teacher".
     */
    private function studentBelongsToTeacherGroup(
        User $student,
        int $courseId,
        int $teacherId,
        ?int $classroomId,
    ): bool {
        $query = $student->classroomsAsStudent()
            ->where('classrooms.course_id', $courseId)
            ->where('classrooms.status', 'active')
            ->wherePivot('status', 'active')
            ->whereHas('teachers', fn ($teacher) => $teacher->whereKey($teacherId))
            // The student's active enrollment must point to this exact cohort.
            // A separate paid enrollment on another cohort must not unlock
            // this teacher's assessment or meeting.
            ->whereExists(function ($enrollment) use ($student, $courseId): void {
                $enrollment->selectRaw('1')
                    ->from('course_enrollments')
                    ->join('courses as enrolled_courses', 'enrolled_courses.id', '=', 'course_enrollments.course_id')
                    ->whereColumn('course_enrollments.classroom_id', 'classrooms.id')
                    ->whereColumn('course_enrollments.course_id', 'classrooms.course_id')
                    ->where('course_enrollments.course_id', $courseId)
                    ->where('course_enrollments.student_id', $student->id)
                    ->where('course_enrollments.status', 'active')
                    ->where(function ($entitlement): void {
                        $entitlement->where('enrolled_courses.access_type', 'free')
                            ->orWhere(function ($paid): void {
                                $paid->where('course_enrollments.payment_status', 'paid')
                                    ->where('course_enrollments.paid_amount', '>', 0)
                                    ->where(function ($amount): void {
                                        $amount->whereNull('course_enrollments.price_amount')
                                            ->orWhereColumn('course_enrollments.paid_amount', '>=', 'course_enrollments.price_amount');
                                    })
                                    ->whereExists(function ($transaction): void {
                                        $transaction->selectRaw('1')
                                            ->from('financial_transactions')
                                            ->whereColumn('financial_transactions.enrollment_id', 'course_enrollments.id')
                                            ->where('financial_transactions.status', 'completed')
                                            ->where('financial_transactions.amount', '>', 0);
                                    });
                            });
                    });
            });

        if ($classroomId !== null) {
            $query->whereKey($classroomId);
        }

        return $query->exists();
    }

    private function academyMember(User $student, int $academyId): bool
    {
        return $student->academies()
            ->whereKey($academyId)
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->exists();
    }
}
