<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\LiveClass;
use App\Models\User;
use App\Services\StudentAccessService;
use App\Support\PersianUi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class StudentDashboardService
{
    public function build(User $student): array
    {
        $access = app(StudentAccessService::class);
        $courseIds = $access->enrolledCourseIds($student);

        $activeCourseCount = $student->enrollments()
            ->where('status', 'active')
            ->whereIn('course_id', $courseIds)
            ->distinct('course_id')
            ->count('course_id');

        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->whereIn('course_id', $courseIds)
            ->with([
                'course:id,academy_id,title,slug,level,access_type,price',
                'course.academy:id,name',
            ])
            ->latest('started_at')
            ->limit(6)
            ->get();

        // The same published course may have multiple teacher-led cohorts.
        // Resolve teachers only through this student's active classroom membership
        // and matching active course enrollment; course.teachers would leak every
        // teacher assigned to the shared course into every student's dashboard.
        $teacherIds = $courseIds->isEmpty()
            ? collect()
            : DB::table('classroom_student as student_membership')
                ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                ->join('course_enrollments as student_enrollment', function ($join) use ($student): void {
                    $join->on('student_enrollment.classroom_id', '=', 'student_classroom.id')
                        ->on('student_enrollment.course_id', '=', 'student_classroom.course_id')
                        ->where('student_enrollment.student_id', '=', $student->id)
                        ->where('student_enrollment.status', '=', 'active');
                })
                ->join('courses as enrolled_courses', 'enrolled_courses.id', '=', 'student_enrollment.course_id')
                ->join('users as teachers', 'teachers.id', '=', 'teaching_assignment.teacher_id')
                ->join('academy_user as teacher_membership', function ($join): void {
                    $join->on('teacher_membership.academy_id', '=', 'enrolled_courses.academy_id')
                        ->on('teacher_membership.user_id', '=', 'teaching_assignment.teacher_id')
                        ->where('teacher_membership.role', '=', 'teacher')
                        ->where('teacher_membership.status', '=', 'active');
                })
                ->whereIn('student_classroom.course_id', $courseIds->all())
                ->where('student_membership.student_id', $student->id)
                ->where('student_membership.status', 'active')
                ->where('student_classroom.status', 'active')
                ->where('teachers.status', 'active')
                ->whereNull('teachers.deleted_at')
                ->distinct()
                ->pluck('teaching_assignment.teacher_id');

        $teachers = $teacherIds->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', $teacherIds)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->with([
                    'teacherProfile.media' => fn ($query) => $query
                        ->wherePivot('collection', 'teacher-avatar')
                        ->orderByPivot('sort_order'),
                ])
                ->orderBy('name')
                ->limit(3)
                ->get(['id', 'name']);

        $progress = $this->progressByCourse($student->id, $courseIds);
        $lessonStats = $this->lessonStatsByCourse($student->id, $courseIds);
        $nextLessons = $this->nextLessonsByCourse($student->id, $courseIds);
        $learningActivity = $this->learningActivity($student->id, $courseIds);

        $courses = $enrollments->map(function ($enrollment) use ($progress, $lessonStats, $nextLessons) {
            $item = $enrollment->course;
            $stats = $lessonStats->get((int) $item->id);

            $item->learning_progress = (float) ($progress[$item->id] ?? 0);
            $item->total_lessons = (int) ($stats?->total_lessons ?? 0);
            $item->completed_lessons = (int) ($stats?->completed_lessons ?? 0);
            $item->next_lesson = $nextLessons->get((int) $item->id);

            return $item;
        });

        $assignmentQuery = $courseIds->isEmpty()
            ? null
            : DB::table('assignments')
                ->leftJoin('assignment_submissions as submissions', function ($join) use ($student): void {
                    $join->on('submissions.assignment_id', '=', 'assignments.id')
                        ->where('submissions.student_id', '=', $student->id);
                })
                ->whereIn('assignments.course_id', $courseIds)
                ->where('assignments.status', 'published')
                ->where(function ($query) use ($student): void {
                    $query->whereNull('assignments.classroom_id')
                        ->orWhereExists(function ($membership) use ($student): void {
                            $membership->selectRaw('1')
                                ->from('classroom_student')
                                ->whereColumn(
                                    'classroom_student.classroom_id',
                                    'assignments.classroom_id'
                                )
                                ->where('classroom_student.student_id', $student->id)
                                ->where('classroom_student.status', 'active');
                        });
                });

        $pendingAssignments = $assignmentQuery
            ? (clone $assignmentQuery)
                ->whereNull('submissions.id')
                ->count('assignments.id')
            : 0;

        if ($assignmentQuery) {
            $this->scopeToStudentTeacherCohort($assignmentQuery, $student, 'assignments');
        }

        $assignmentItems = $assignmentQuery
            ? (clone $assignmentQuery)
                ->orderByRaw('CASE WHEN submissions.id IS NULL THEN 0 ELSE 1 END')
                ->orderBy('assignments.due_at')
                ->limit(6)
                ->get([
                    'assignments.id',
                    'assignments.title',
                    'assignments.due_at',
                    'submissions.submitted_at',
                    'submissions.score',
                    'submissions.graded_at',
                ])
                ->map(function ($assignment): object {
                    if ($assignment->due_at !== null) {
                        $assignment->due_at = Carbon::parse($assignment->due_at);
                    }

                    if ($assignment->submitted_at !== null) {
                        $assignment->submitted_at = Carbon::parse($assignment->submitted_at);
                    }

                    if ($assignment->graded_at !== null) {
                        $assignment->graded_at = Carbon::parse($assignment->graded_at);
                    }

                    return $assignment;
                })
            : collect();

        $upcomingLive = $courseIds->isEmpty()
            ? collect()
            : DB::table('live_classes')
                ->join('courses', 'courses.id', '=', 'live_classes.course_id')
                ->leftJoin('classroom_student', function ($join) use ($student): void {
                    $join->on('classroom_student.classroom_id', '=', 'live_classes.classroom_id')
                        ->where('classroom_student.student_id', '=', $student->id)
                        ->where('classroom_student.status', '=', 'active');
                })
                ->whereIn('live_classes.course_id', $courseIds)
                ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
                ->where('live_classes.status', 'scheduled')
                ->where(function ($query) {
                    $query->whereNull('live_classes.classroom_id')
                        ->orWhereNotNull('classroom_student.student_id');
                })
                ->orderBy('live_classes.scheduled_at')
                ->limit(5)
                ->get([
                    'live_classes.id',
                    'live_classes.title',
                    'live_classes.scheduled_at',
                    'courses.title as course_title',
                ])
                ->map(function ($class): object {
                    $class->scheduled_at = $class->scheduled_at
                        ? Carbon::parse($class->scheduled_at)
                        : null;

                    return $class;
                });


        $upcomingMeetings = DB::table('academy_meetings')
            ->join('courses', 'courses.id', '=', 'academy_meetings.course_id')
            ->leftJoin('classrooms', 'classrooms.id', '=', 'academy_meetings.classroom_id')
            ->where('academy_meetings.student_id', $student->id)
            ->where('academy_meetings.status', 'scheduled')
            ->whereBetween('academy_meetings.scheduled_at', [now(), now()->addDays(30)])
            ->orderBy('academy_meetings.scheduled_at')
            ->limit(6)
            ->get([
                'academy_meetings.id',
                'academy_meetings.title',
                'academy_meetings.message',
                'academy_meetings.scheduled_at',
                'courses.title as course_title',
                'classrooms.title as classroom_title',
            ])
            ->map(function ($item): object {
                $item->scheduled_at = $item->scheduled_at instanceof \Carbon\CarbonInterface
                    ? $item->scheduled_at
                    : Carbon::parse($item->scheduled_at);
                return $item;
            });

        $sessions = $courseIds->isEmpty()
            ? collect()
            : LiveClass::query()
                ->whereIn('course_id', $courseIds)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('scheduled_at', [now()->subDays(60), now()->addDays(60)])
                ->where(function ($query) use ($student): void {
                    $query->whereNull('classroom_id')
                        ->orWhereExists(function ($membership) use ($student): void {
                            $membership->selectRaw('1')
                                ->from('classroom_student')
                                ->whereColumn('classroom_student.classroom_id', 'live_classes.classroom_id')
                                ->where('classroom_student.student_id', $student->id)
                                ->where('classroom_student.status', 'active');
                        });
                })
                ->with(['course:id,title', 'classroom:id,title', 'recording:id,disk,path,visibility,status'])
                ->orderByDesc('scheduled_at')
                ->limit(24)
                ->get()
                ->map(function (LiveClass $session): array {
                    $isFuture = $session->scheduled_at->isFuture();

                    $recordingReady = !$isFuture
                        && $session->isRecordingAvailable()
                        && $session->recording?->status === 'active'
                        && $session->recording?->visibility === 'private';

                    return [
                        'id' => $session->id,
                        'title' => $session->title,
                        'course' => $session->course?->title,
                        'classroom' => $session->classroom?->title,
                        'date' => PersianUi::date($session->scheduled_at),
                        'time' => PersianUi::time($session->scheduled_at),
                        'is_future' => $isFuture,
                        'is_past' => $session->scheduled_at->isPast(),
                        'status' => $session->status,
                        'lamp' => $recordingReady ? 'روشن' : 'خاموش',
                        'available' => $recordingReady,
                        'href' => $recordingReady ? route('media.view', $session->recording) : null,
                    ];
                });

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('submissions.student_id', $student->id)
            ->whereIn('assignments.course_id', $courseIds);
        $this->scopeToStudentTeacherCohort($assignmentResults, $student, 'assignments');
        $assignmentResults = $assignmentResults
            ->whereNotNull('submissions.graded_at')
            ->latest('submissions.graded_at')
            ->limit(6)
            ->get([
                'assignments.id as target_id',
                'assignments.title',
                'submissions.score',
                'submissions.graded_at as occurred_at',
            ])
            ->map(function ($result): object {
                $result->occurred_at = $result->occurred_at
                    ? Carbon::parse($result->occurred_at)
                    : null;
                $result->type = 'assignment';
                $result->status_label = 'تصحیح‌شده';
                return $result;
            });

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->where('attempts.student_id', $student->id)
            ->whereIn('exams.course_id', $courseIds);
        $this->scopeToStudentTeacherCohort($examResults, $student, 'exams');
        $examResults = $examResults
            ->whereNotNull('attempts.submitted_at')
            ->latest('attempts.submitted_at')
            ->limit(6)
            ->get([
                'exams.id as target_id',
                'exams.title',
                'attempts.score',
                'attempts.submitted_at as occurred_at',
                'attempts.status',
            ])
            ->map(function ($result): object {
                $result->occurred_at = $result->occurred_at
                    ? Carbon::parse($result->occurred_at)
                    : null;
                $result->type = 'exam';
                $result->status_label = match ($result->status) {
                    'graded' => 'تصحیح‌شده',
                    'needs_review' => 'نمره موقت · در انتظار تصحیح تشریحی',
                    'pending_review' => 'در صف تصحیح مدرس',
                    default => 'ارسال‌شده',
                };
                // A mixed exam may have an objective-only provisional score while
                // its essay answers are still waiting for the teacher.
                $result->is_provisional = $result->status === 'needs_review' && $result->score !== null;
                if (!in_array($result->status, ['graded', 'needs_review'], true)) {
                    $result->score = null;
                }
                return $result;
            });

        $recentResults = $assignmentResults
            ->concat($examResults)
            ->sortByDesc('occurred_at')
            ->take(6)
            ->values();

        $resources = app(StudentLearningResourceService::class)
            ->query($student)
            ->limit(4)
            ->get();

        $achievements = Achievement::query()
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->latest('id')
            ->limit(3)
            ->get([
                'id',
                'title',
                'display_name',
                'achievement_type',
                'school_name',
                'published_at',
            ]);

        $achievementCount = Achievement::query()
            ->where('student_id', $student->id)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->count();

        $overallProgress = (float) ($progress->isEmpty() ? 0 : $progress->avg());

        $completedLessonsCount = (int) $lessonStats->sum('completed_lessons');
        $totalLessonsCount = (int) $lessonStats->sum('total_lessons');

        return [
            'student' => $student,
            'profile' => $student->studentProfile,
            'courses' => $courses,
            'teachers' => $teachers,
            'overallProgress' => round($overallProgress),
            'activeCourseCount' => $activeCourseCount,
            'pendingAssignments' => $pendingAssignments,
            'upcomingLiveClasses' => $upcomingLive,
            'upcomingMeetings' => $upcomingMeetings,
            'assignments' => $assignmentItems,
            'recentResults' => $recentResults,
            'resources' => $resources,
            'sessions' => $sessions,
            'nextLesson' => $courses->pluck('next_lesson')->filter()->first(),
            'nextLiveClass' => $upcomingLive->first(),
            'completedLessonsCount' => $completedLessonsCount,
            'totalLessonsCount' => $totalLessonsCount,
            'studyMinutesLast7Days' => $learningActivity['study_minutes_last_7_days'],
            'studyStreak' => $learningActivity['study_streak'],
            'studyDates' => $learningActivity['study_dates'],
            'studyWeek' => $learningActivity['study_week'],
            'achievements' => $achievements,
            'achievementCount' => $achievementCount,
        ];
    }

    /**
     * Keep every teacher-authored dashboard assessment inside the student's
     * active teacher-led cohort. Course membership alone is not enough when a
     * course is shared by multiple classrooms and teachers.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    private function scopeToStudentTeacherCohort(
        \Illuminate\Database\Query\Builder $query,
        User $student,
        string $assessmentTable
    ): void {
        $query->whereExists(function ($group) use ($student, $assessmentTable): void {
            $group->selectRaw('1')
                ->from('classroom_student as student_membership')
                ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                ->join('course_enrollments as student_enrollment', function ($join) use ($student): void {
                    $join->on('student_enrollment.classroom_id', '=', 'student_classroom.id')
                        ->on('student_enrollment.course_id', '=', 'student_classroom.course_id')
                        ->where('student_enrollment.student_id', '=', $student->id)
                        ->where('student_enrollment.status', '=', 'active');
                })
                ->whereColumn('student_classroom.course_id', $assessmentTable . '.course_id')
                ->whereColumn('teaching_assignment.teacher_id', $assessmentTable . '.teacher_id')
                ->where('student_membership.student_id', $student->id)
                ->where('student_membership.status', 'active')
                ->where('student_classroom.status', 'active')
                ->where(function ($scope) use ($assessmentTable): void {
                    $scope->whereNull($assessmentTable . '.classroom_id')
                        ->orWhereColumn($assessmentTable . '.classroom_id', 'student_classroom.id');
                });
        });
    }

    private function lessonStatsByCourse(int $studentId, $courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('course_sections')
            ->join('lessons', 'lessons.course_section_id', '=', 'course_sections.id')
            ->leftJoin('lesson_progress as progress', function ($join) use ($studentId): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->where('progress.user_id', '=', $studentId);
            })
            ->whereIn('course_sections.course_id', $courseIds)
            ->where('lessons.status', 'published')
            ->where(fn ($query) => $query->whereNull('lessons.published_at')->orWhere('lessons.published_at', '<=', now()))
            ->groupBy('course_sections.course_id')
            ->select('course_sections.course_id')
            ->selectRaw('COUNT(lessons.id) as total_lessons')
            ->selectRaw('SUM(CASE WHEN COALESCE(progress.progress_percent, 0) >= 100 THEN 1 ELSE 0 END) as completed_lessons')
            ->get()
            ->keyBy(fn ($row) => (int) $row->course_id);
    }

    private function nextLessonsByCourse(int $studentId, $courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('course_sections')
            ->join('lessons', 'lessons.course_section_id', '=', 'course_sections.id')
            ->leftJoin('lesson_progress as progress', function ($join) use ($studentId): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->where('progress.user_id', '=', $studentId);
            })
            ->whereIn('course_sections.course_id', $courseIds)
            ->where('lessons.status', 'published')
            ->where(fn ($query) => $query->whereNull('lessons.published_at')->orWhere('lessons.published_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('progress.progress_percent')->orWhere('progress.progress_percent', '<', 100))
            ->whereNotExists(function ($query) use ($studentId): void {
                $query->selectRaw('1')
                    ->from('course_sections as earlier_sections')
                    ->join('lessons as earlier_lessons', 'earlier_lessons.course_section_id', '=', 'earlier_sections.id')
                    ->leftJoin('lesson_progress as earlier_progress', function ($join) use ($studentId): void {
                        $join->on('earlier_progress.lesson_id', '=', 'earlier_lessons.id')
                            ->where('earlier_progress.user_id', '=', $studentId);
                    })
                    ->whereColumn('earlier_sections.course_id', 'course_sections.course_id')
                    ->where('earlier_lessons.status', 'published')
                    ->where(fn ($query) => $query->whereNull('earlier_lessons.published_at')->orWhere('earlier_lessons.published_at', '<=', now()))
                    ->where(fn ($query) => $query->whereNull('earlier_progress.progress_percent')->orWhere('earlier_progress.progress_percent', '<', 100))
                    ->where(function ($query): void {
                        $query->whereColumn('earlier_sections.sort_order', '<', 'course_sections.sort_order')
                            ->orWhere(function ($tie) {
                                $tie->whereColumn('earlier_sections.sort_order', '=', 'course_sections.sort_order')
                                    ->whereColumn('earlier_lessons.sort_order', '<', 'lessons.sort_order');
                            })
                            ->orWhere(function ($tie) {
                                $tie->whereColumn('earlier_sections.sort_order', '=', 'course_sections.sort_order')
                                    ->whereColumn('earlier_lessons.sort_order', '=', 'lessons.sort_order')
                                    ->whereColumn('earlier_lessons.id', '<', 'lessons.id');
                            });
                    });
            })
            ->orderBy('course_sections.course_id')
            ->get([
                'course_sections.course_id',
                'lessons.id',
                'lessons.title',
                'lessons.type',
                'lessons.duration_seconds',
            ])
            ->keyBy(fn ($row) => (int) $row->course_id);
    }

    private function learningActivity(int $studentId, $courseIds): array
    {
        if ($courseIds->isEmpty()) {
            return [
                'study_minutes_last_7_days' => 0,
                'study_streak' => 0,
                'study_dates' => [],
                'study_week' => $this->emptyStudyWeek(),
            ];
        }

        $rows = DB::table('lesson_progress')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('lesson_progress.user_id', $studentId)
            ->whereIn('course_sections.course_id', $courseIds)
            ->whereNotNull('lesson_progress.last_watched_at')
            ->where('lesson_progress.last_watched_at', '>=', now()->subDays(13)->startOfDay())
            ->orderByDesc('lesson_progress.last_watched_at')
            ->get([
                'lesson_progress.seconds_watched',
                'lesson_progress.last_watched_at',
            ]);

        $studyDates = $rows
            ->map(fn ($row) => Carbon::parse($row->last_watched_at)->toDateString())
            ->unique()
            ->values();

        $recentCutoff = now()->subDays(6)->startOfDay();
        $studySeconds = $rows
            ->filter(fn ($row) => Carbon::parse($row->last_watched_at)->greaterThanOrEqualTo($recentCutoff))
            ->sum(fn ($row) => (int) $row->seconds_watched);

        $anchor = Carbon::today();
        if (!$studyDates->contains($anchor->toDateString())) {
            $yesterday = $anchor->copy()->subDay();
            if (!$studyDates->contains($yesterday->toDateString())) {
                return [
                    'study_minutes_last_7_days' => (int) floor($studySeconds / 60),
                    'study_streak' => 0,
                    'study_dates' => $studyDates->all(),
                    'study_week' => $this->studyWeek($rows),
                ];
            }
            $anchor = $yesterday;
        }

        $streak = 0;
        while ($studyDates->contains($anchor->toDateString())) {
            $streak++;
            $anchor->subDay();
        }

        return [
            'study_minutes_last_7_days' => (int) floor($studySeconds / 60),
            'study_streak' => $streak,
            'study_dates' => $studyDates->all(),
            'study_week' => $this->studyWeek($rows),
        ];
    }

    private function emptyStudyWeek(): array
    {
        $week = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $date = now()->startOfDay()->subDays($offset);

            $week[] = [
                'date' => $date->toDateString(),
                'label' => PersianUi::digits($date->day),
                'weekday' => $this->persianWeekday($date->dayOfWeek),
                'minutes' => 0,
            ];
        }

        return $week;
    }

    private function studyWeek($rows): array
    {
        $minutesByDate = collect($rows)
            ->groupBy(fn ($row) => Carbon::parse($row->last_watched_at)->toDateString())
            ->map(fn ($items) => (int) floor($items->sum(fn ($row) => (int) $row->seconds_watched) / 60));

        $week = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $date = now()->startOfDay()->subDays($offset);
            $key = $date->toDateString();

            $week[] = [
                'date' => $key,
                'label' => PersianUi::digits($date->day),
                'weekday' => $this->persianWeekday($date->dayOfWeek),
                'minutes' => (int) ($minutesByDate[$key] ?? 0),
            ];
        }

        return $week;
    }

    private function persianWeekday(int $dayOfWeek): string
    {
        return [
            0 => 'ی',
            1 => 'د',
            2 => 'س',
            3 => 'چ',
            4 => 'پ',
            5 => 'ج',
            6 => 'ش',
        ][$dayOfWeek] ?? '';
    }

    private function progressByCourse(int $studentId, $courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('course_sections')
            ->join('lessons', 'lessons.course_section_id', '=', 'course_sections.id')
            ->leftJoin('lesson_progress as progress', function ($join) use ($studentId): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->where('progress.user_id', '=', $studentId);
            })
            ->whereIn('course_sections.course_id', $courseIds)
            ->where('lessons.status', 'published')
            ->where(fn ($query) => $query->whereNull('lessons.published_at')->orWhere('lessons.published_at', '<=', now()))
            ->groupBy('course_sections.course_id')
            ->select('course_sections.course_id')
            ->selectRaw('AVG(COALESCE(progress.progress_percent, 0)) as progress_average')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->course_id => (float) $row->progress_average,
            ]);
    }
}
