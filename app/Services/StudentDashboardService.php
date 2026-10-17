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
                'course.academy:id,name',
            ])
            ->latest('started_at')
            ->limit(6)
            ->get();

        $progress = $this->progressByCourse($student->id, $courseIds);
        $lessonStats = $this->lessonStatsByCourse($student->id, $courseIds);
        $nextLessons = $this->nextLessonsByCourse($student->id, $courseIds);
        $learningActivity = $this->learningActivity($student->id, $courseIds);
        $attendanceMetrics = app(AttendanceMetricsService::class)->forStudent($student);

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
                ->whereExists(function ($group) use ($student): void {
                    $group->selectRaw('1')
                        ->from('classroom_student as student_membership')
                        ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                        ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                        ->whereColumn('student_classroom.course_id', 'assignments.course_id')
                        ->whereColumn('teaching_assignment.teacher_id', 'assignments.teacher_id')
                        ->where('student_classroom.status', 'active')
                        ->where('student_membership.student_id', $student->id)
                        ->where('student_membership.status', 'active')
                        ->where(function ($scope): void {
                            $scope->whereNull('assignments.classroom_id')
                                ->orWhereColumn('assignments.classroom_id', 'student_classroom.id');
                        });
                });

        $pendingAssignments = $assignmentQuery
            ? (clone $assignmentQuery)
                // A draft submission row is still waiting for the student to
                // press submit; the existence of a row alone is not completion.
                ->whereNull('submissions.submitted_at')
                ->count('assignments.id')
            : 0;

        $assignmentItems = $assignmentQuery
            ? (clone $assignmentQuery)
                ->orderByRaw('CASE WHEN submissions.submitted_at IS NULL THEN 0 ELSE 1 END')
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
                ->whereIn('live_classes.course_id', $courseIds)
                ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
                ->where('live_classes.status', 'scheduled')
                ->whereExists(function ($group) use ($student): void {
                    $group->selectRaw('1')
                        ->from('classroom_student as student_membership')
                        ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                        ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                        ->whereColumn('student_classroom.course_id', 'live_classes.course_id')
                        ->whereColumn('teaching_assignment.teacher_id', 'live_classes.teacher_id')
                        ->where('student_classroom.status', 'active')
                        ->where('student_membership.student_id', $student->id)
                        ->where('student_membership.status', 'active')
                        ->where(function ($scope): void {
                            $scope->whereNull('live_classes.classroom_id')
                                ->orWhereColumn('live_classes.classroom_id', 'student_classroom.id');
                        });
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

        $sessions = $courseIds->isEmpty()
            ? collect()
            : LiveClass::query()
                ->whereIn('course_id', $courseIds)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('scheduled_at', [now()->subDays(60), now()->addDays(60)])
                ->whereExists(function ($group) use ($student): void {
                    $group->selectRaw('1')
                        ->from('classroom_student as student_membership')
                        ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                        ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                        ->whereColumn('student_classroom.course_id', 'live_classes.course_id')
                        ->whereColumn('teaching_assignment.teacher_id', 'live_classes.teacher_id')
                        ->where('student_classroom.status', 'active')
                        ->where('student_membership.student_id', $student->id)
                        ->where('student_membership.status', 'active')
                        ->where(function ($scope): void {
                            $scope->whereNull('live_classes.classroom_id')
                                ->orWhereColumn('live_classes.classroom_id', 'student_classroom.id');
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
            ->whereIn('assignments.course_id', $courseIds)
            ->whereNotNull('submissions.graded_at')
            ->latest('submissions.graded_at')
            ->limit(6)
            ->get([
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
            ->whereIn('exams.course_id', $courseIds)
            ->whereNotNull('attempts.submitted_at')
            ->latest('attempts.submitted_at')
            ->limit(6)
            ->get([
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
                $result->status_label = $result->status === 'graded'
                    ? 'تصحیح‌شده'
                    : 'در انتظار بررسی';
                if ($result->status !== 'graded') {
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

        // Show only educators assigned to the student's exact active classroom cohort.
        // A public course may have multiple teachers serving different groups.
        $teachers = $courseIds->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', function ($query) use ($student, $courseIds): void {
                    $query->select('classroom_teachers.teacher_id')
                        ->from('classroom_teacher as classroom_teachers')
                        ->join('classrooms', 'classrooms.id', '=', 'classroom_teachers.classroom_id')
                        ->join('classroom_student as roster', function ($join) use ($student): void {
                            $join->on('roster.classroom_id', '=', 'classrooms.id')
                                ->where('roster.student_id', '=', $student->id)
                                ->where('roster.status', '=', 'active');
                        })
                        ->join('course_enrollments as enrollments', function ($join) use ($student): void {
                            $join->on('enrollments.classroom_id', '=', 'classrooms.id')
                                ->on('enrollments.course_id', '=', 'classrooms.course_id')
                                ->where('enrollments.student_id', '=', $student->id)
                                ->where('enrollments.status', '=', 'active');
                        })
                        ->join('course_teacher as assigned_course_teachers', function ($join): void {
                            $join->on('assigned_course_teachers.course_id', '=', 'classrooms.course_id')
                                ->on('assigned_course_teachers.teacher_id', '=', 'classroom_teachers.teacher_id');
                        })
                        ->join('academy_user as academy_membership', function ($join): void {
                            $join->on('academy_membership.user_id', '=', 'classroom_teachers.teacher_id')
                                ->on('academy_membership.academy_id', '=', 'classrooms.academy_id')
                                ->where('academy_membership.role', '=', 'teacher')
                                ->where('academy_membership.status', '=', 'active');
                        })
                        ->whereIn('enrollments.course_id', $courseIds)
                        ->where('classrooms.status', '=', 'active')
                        ->distinct();
                })
                ->whereHas('roles', fn ($query) => $query->where('slug', 'teacher'))
                ->orderBy('name')
                ->limit(3)
                ->get(['id', 'name']);

        return [
            'student' => $student,
            'profile' => $student->studentProfile,
            'courses' => $courses,
            'teachers' => $teachers,
            'overallProgress' => round($overallProgress),
            'attendanceMetrics' => $attendanceMetrics,
            'activeCourseCount' => $activeCourseCount,
            'pendingAssignments' => $pendingAssignments,
            'upcomingLiveClasses' => $upcomingLive,
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
