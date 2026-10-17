<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Models\User;
use App\Support\PersianUi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class TeacherDashboardService
{
    public function build(User $teacher): array
    {
        $activeAcademyIds = $teacher->academies()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        $courseIds = $teacher->taughtCourses()
            ->whereIn('courses.academy_id', $activeAcademyIds)
            ->pluck('courses.id');

        $classrooms = $teacher->classroomsAsTeacher()
            ->whereIn('classrooms.academy_id', $activeAcademyIds)
            ->where('classrooms.status', 'active')
            ->with([
                'course:id,title',
                'schedules' => fn ($query) => $query->orderBy('start_time'),
            ])
            ->get();

        $classroomIds = $classrooms->modelKeys();

        $studentCount = $classroomIds
            ? DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->distinct()
                ->count('student_id')
            : 0;

        if ($courseIds->isEmpty()) {
            return $this->emptyState($teacher, $classrooms, $studentCount);
        }

        $weekStart = now()->startOfWeek(Carbon::SATURDAY);
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY);
        $weeklyProgress = $this->progressAverage($teacher->id, $courseIds);
        $pendingAssignmentReviews = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('assignments.teacher_id', $teacher->id)
            ->whereIn('assignments.course_id', $courseIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->count();

        $pendingExamReviews = ExamAttempt::query()
            // These are all statuses rendered as awaiting review in the grading UI.
            ->whereIn('status', ['submitted', 'pending_review', 'needs_review'])
            ->whereHas('exam', fn ($query) => $query
                ->where('teacher_id', $teacher->id)
                ->whereIn('course_id', $courseIds))
            ->count();

        $weeklySessions = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->where('status', '!=', 'cancelled')
            ->count();

        $completedSessions = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
            ->whereNotNull('ended_at')
            ->where('status', '!=', 'cancelled')
            ->count();

        $todaySessions = $this->todaySessions($teacher, $classrooms, $courseIds);
        $upcomingClasses = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_at', [now(), now()->addDays(7)])
            ->where('status', 'scheduled')
            ->with([
                'course:id,title',
                'classroom:id,title',
            ])
            ->orderBy('scheduled_at')
            ->limit(6)
            ->get();

        $weekChart = $this->weeklyChart($teacher->id, $courseIds, $weekStart, $weekEnd);

        $chart = [
            'week' => $weekChart,
            'month' => $this->monthlyChart($teacher->id, $courseIds),
        ];

        $activities = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('course_id', $courseIds)
            ->with('classroom:id,title')
            ->withCount([
                'submissions as submitted_count' => fn ($query) => $query->whereNotNull('submitted_at'),
                'submissions as pending_review_count' => fn ($query) => $query
                    ->whereNotNull('submitted_at')
                    ->whereNull('graded_at'),
            ])
            ->latest('due_at')
            ->limit(6)
            ->get();

        $courseProgress = $this->courseProgress($teacher->id, $courseIds);
        $topStudents = DB::table('classroom_student as memberships')
            ->join('classrooms', 'classrooms.id', '=', 'memberships.classroom_id')
            ->join('classroom_teacher as classroom_teachers', function ($join) use ($teacher): void {
                $join->on('classroom_teachers.classroom_id', '=', 'memberships.classroom_id')
                    ->where('classroom_teachers.teacher_id', '=', $teacher->id);
            })
            ->join('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.student_id', '=', 'memberships.student_id')
                    ->on('enrollments.classroom_id', '=', 'classrooms.id')
                    ->on('enrollments.course_id', '=', 'classrooms.course_id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->join('users', 'users.id', '=', 'memberships.student_id')
            ->leftJoin('course_sections', 'course_sections.course_id', '=', 'enrollments.course_id')
            ->leftJoin('lessons', 'lessons.course_section_id', '=', 'course_sections.id')
            ->leftJoin('lesson_progress as progress', function ($join): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->on('progress.user_id', '=', 'enrollments.student_id');
            })
            ->where('memberships.status', 'active')
            ->whereIn('enrollments.course_id', $courseIds)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc(DB::raw('COALESCE(AVG(progress.progress_percent), 0)'))
            ->limit(3)
            ->get([
                'users.id',
                'users.name',
                DB::raw('ROUND(COALESCE(AVG(progress.progress_percent), 0)) as progress_average'),
            ])
            ->map(function ($student): object {
                $student->progress_average = (int) $student->progress_average;
                return $student;
            });


        return [
            'teacher' => $teacher,
            'profile' => $teacher->teacherProfile,
            'dashboardDate' => PersianUi::date(now()),
            'weeklyProgress' => round((float) $weeklyProgress),
            'completedSessions' => $completedSessions,
            'metrics' => [
                'activeClasses' => count($classroomIds),
                'studentCount' => $studentCount,
                'weeklySessions' => $weeklySessions,
                'pendingAssignmentReviews' => $pendingAssignmentReviews,
                'pendingExamReviews' => $pendingExamReviews,
                'pendingReviews' => $pendingAssignmentReviews + $pendingExamReviews,
            ],
            'todaySessions' => $todaySessions,
            'upcomingClasses' => $upcomingClasses,
            'chart' => $chart,
            'activities' => $activities,
            'courseProgress' => $courseProgress,
            'topStudents' => $topStudents,
            'calendarEvents' => $this->calendarEvents($teacher, $courseIds),
        ];
    }

private function exclusiveCourseIdsForTeacher(int $teacherId, $courseIds)
    {
        return DB::table('course_teacher')
            ->select('course_id')
            ->whereIn('course_id', $courseIds)
            ->groupBy('course_id')
            ->havingRaw('COUNT(DISTINCT teacher_id) = 1')
            ->havingRaw('MAX(teacher_id) = ?', [$teacherId])
            ->pluck('course_id');
    }

    /**
     * One row per eligible student × published lesson. Class-based learners are
     * included only when this teacher is assigned to that exact classroom and
     * its roster membership is active. A classroom-less enrollment is visible
     * only when the course has one assigned teacher; otherwise ownership of a
     * shared public course must not expose another teacher's cohort.
     */
    private function teacherLearningMatrix(int $teacherId, $courseIds)
    {
        $exclusiveCourseIds = $this->exclusiveCourseIdsForTeacher($teacherId, $courseIds);

        return DB::table('course_teacher as course_teachers')
            ->join('courses', 'courses.id', '=', 'course_teachers.course_id')
            ->join('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->leftJoin('classrooms', function ($join): void {
                $join->on('classrooms.id', '=', 'enrollments.classroom_id')
                    ->on('classrooms.course_id', '=', 'courses.id')
                    ->where('classrooms.status', '=', 'active');
            })
            ->leftJoin('classroom_teacher as assigned_teachers', function ($join) use ($teacherId): void {
                $join->on('assigned_teachers.classroom_id', '=', 'classrooms.id')
                    ->where('assigned_teachers.teacher_id', '=', $teacherId);
            })
            ->leftJoin('classroom_student as memberships', function ($join): void {
                $join->on('memberships.classroom_id', '=', 'classrooms.id')
                    ->on('memberships.student_id', '=', 'enrollments.student_id')
                    ->where('memberships.status', '=', 'active');
            })
            ->leftJoin('course_sections', 'course_sections.course_id', '=', 'courses.id')
            ->leftJoin('lessons', function ($join): void {
                $join->on('lessons.course_section_id', '=', 'course_sections.id')
                    ->where('lessons.status', '=', 'published')
                    ->where(function ($published): void {
                        $published->whereNull('lessons.published_at')
                            ->orWhere('lessons.published_at', '<=', now());
                    });
            })
            ->leftJoin('lesson_progress as progress', function ($join): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->on('progress.user_id', '=', 'enrollments.student_id');
            })
            ->where('course_teachers.teacher_id', $teacherId)
            ->whereIn('course_teachers.course_id', $courseIds)
            ->where(function ($scope) use ($exclusiveCourseIds): void {
                $scope->where(function ($classroomScope): void {
                    $classroomScope->whereNotNull('assigned_teachers.classroom_id')
                        ->whereNotNull('memberships.student_id');
                });

                if ($exclusiveCourseIds->isNotEmpty()) {
                    $scope->orWhere(function ($directEnrollment) use ($exclusiveCourseIds): void {
                        $directEnrollment->whereNull('enrollments.classroom_id')
                            ->whereIn('courses.id', $exclusiveCourseIds);
                    });
                }
            });
    }

    private function progressAverage(int $teacherId, $courseIds): float
    {
        return (float) ($this->teacherLearningMatrix($teacherId, $courseIds)
            ->avg(DB::raw('COALESCE(progress.progress_percent, 0)')) ?? 0);
    }

    private function courseProgress(int $teacherId, $courseIds)
    {
        return $this->teacherLearningMatrix($teacherId, $courseIds)
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('courses.id')
            ->get([
                'courses.id as course_id',
                'courses.title',
                DB::raw('COUNT(DISTINCT enrollments.student_id) AS student_count'),
                DB::raw('ROUND(COALESCE(AVG(COALESCE(progress.progress_percent, 0)), 0)) AS progress_average'),
            ])
            ->map(function ($row): object {
                $row->student_count = (int) $row->student_count;
                $row->progress_average = round((float) $row->progress_average);

                return $row;
            });
    }

    private function weeklyChart(int $teacherId, $courseIds, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $values = [];
        $dayMap = [0 => 'ی', 1 => 'د', 2 => 'س', 3 => 'چ', 4 => 'پ', 5 => 'ج', 6 => 'ش'];

        $rows = $this->teacherLearningMatrix($teacherId, $courseIds)
            ->whereNotNull('progress.last_watched_at')
            ->whereBetween('progress.last_watched_at', [$from, $to])
            ->groupBy(DB::raw('DATE(progress.last_watched_at)'))
            ->selectRaw('DATE(progress.last_watched_at) as day, AVG(progress.progress_percent) as value')
            ->pluck('value', 'day');

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $dayMap[$date->dayOfWeek];
            $values[] = round((float) ($rows[$key] ?? 0));
        }

        return compact('labels', 'values');
    }

    private function monthlyChart(int $teacherId, $courseIds): array
    {
        $from = now()->subDays(30)->startOfDay();
        $to = now()->endOfDay();

        $weekExpression = match (DB::getDriverName()) {
            'sqlite' => "strftime('%W', progress.last_watched_at)",
            'mysql', 'mariadb' => "WEEK(progress.last_watched_at, 1)",
            'pgsql' => "EXTRACT(WEEK FROM progress.last_watched_at)",
            default => "EXTRACT(WEEK FROM progress.last_watched_at)",
        };

        $rows = $this->teacherLearningMatrix($teacherId, $courseIds)
            ->whereNotNull('progress.last_watched_at')
            ->whereBetween('progress.last_watched_at', [$from, $to])
            ->selectRaw("{$weekExpression} as week_number, AVG(progress.progress_percent) as value")
            ->groupBy(DB::raw($weekExpression))
            ->pluck('value', 'week_number');

        $labels = [];
        $values = [];

        for ($index = 3; $index >= 0; $index--) {
            $date = now()->subWeeks($index);
            $week = match (DB::getDriverName()) {
                'pgsql' => (string) (int) $date->format('W'),
                default => (int) $date->format('W'),
            };

            $labels[] = $this->faDigits('هفته ' . (4 - $index));
            $values[] = round((float) ($rows[$week] ?? 0));
        }

        while (count($values) < 7) {
            array_unshift($values, $values[0] ?? 0);
            array_unshift($labels, '');
        }

        return ['labels' => $labels, 'values' => $values];
    }

        private function todaySessions(User $teacher, $classrooms, $courseIds)
    {
        $weekday = now()->dayOfWeek;

        $scheduled = $classrooms->flatMap(function ($classroom) use ($weekday) {
            return $classroom->schedules
                ->where('weekday', $weekday)
                ->map(function ($schedule) use ($classroom) {
                    $start = Carbon::parse($schedule->start_time);
                    $end = Carbon::parse($schedule->end_time);
                    $now = now();

                    $startAt = now()->copy()->setTimeFrom($start);
                    $endAt = now()->copy()->setTimeFrom($end);

                    $status = $now->gt($endAt)
                        ? 'پایان یافته'
                        : ($now->gte($startAt) ? 'در حال برگزاری' : 'شروع نشده');

                    return [
                        'time' => $this->faDigits($start->format('H:i')),
                        'title' => $classroom->course?->title ?? $classroom->title,
                        'meta' => $classroom->title,
                        'status' => $status,
                        'type' => 'class',
                    ];
                });
        });

        $live = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])
            ->where('status', '!=', 'cancelled')
            ->with(['course:id,title', 'classroom:id,title'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(function (LiveClass $item) {
                $ended = $item->ended_at !== null
                    || ($item->scheduled_end_at !== null && $item->scheduled_end_at->isPast());

                return [
                    'time' => $this->faDigits($item->scheduled_at->format('H:i')),
                    'title' => $item->title,
                    'meta' => $item->classroom?->title ?? $item->course?->title,
                    'status' => $ended
                        ? 'پایان یافته'
                        : ($item->scheduled_at->isPast() ? 'در حال برگزاری' : 'شروع نشده'),
                    'type' => 'online',
                ];
            });

        return $scheduled->concat($live)->sortBy('time')->values();
    }

    private function calendarEvents(User $teacher, $courseIds): array
    {
        $from = now()->startOfDay();
        $to = now()->addMonth()->endOfDay();

        $events = [];

        LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereIn('course_id', $courseIds)
            ->whereBetween('scheduled_at', [$from, $to])
            ->get(['scheduled_at', 'title'])
            ->each(function ($event) use (&$events): void {
                $events[$event->scheduled_at->format('Y-m-d')][] = [
                    'type' => 'live',
                    'title' => $event->title,
                ];
            });

        Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('course_id', $courseIds)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$from, $to])
            ->get(['due_at', 'title'])
            ->each(function ($event) use (&$events): void {
                $events[$event->due_at->format('Y-m-d')][] = [
                    'type' => 'assignment',
                    'title' => $event->title,
                ];
            });

        return $events;
    }

    private function emptyState(User $teacher, $classrooms, int $studentCount): array
    {
        return [
            'teacher' => $teacher,
            'profile' => $teacher->teacherProfile,
            'dashboardDate' => PersianUi::date(now()),
            'weeklyProgress' => 0,
            'completedSessions' => 0,
            'metrics' => [
                'activeClasses' => $classrooms->count(),
                'studentCount' => $studentCount,
                'weeklySessions' => 0,
                'pendingAssignmentReviews' => 0,
                'pendingExamReviews' => 0,
                'pendingReviews' => 0,
            ],
            'todaySessions' => collect(),
            'upcomingClasses' => collect(),
            'chart' => [
                'week' => ['labels' => [], 'values' => []],
                'month' => ['labels' => [], 'values' => []],
            ],
            'activities' => collect(),
            'courseProgress' => collect(),
            'topStudents' => collect(),
            'calendarEvents' => [],
        ];
    }

    private function faDigits($value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }
}
