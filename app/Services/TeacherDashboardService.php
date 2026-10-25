<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Models\User;
use App\Support\PersianUi;
use App\Services\MeetingLinkService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class TeacherDashboardService
{
    public function build(User $teacher): array
    {
        $activeAcademyIds = $teacher->academies()
            ->where('academies.status', 'active')
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
            ? DB::table('classroom_student as memberships')
                ->join('users', 'users.id', '=', 'memberships.student_id')
                ->whereIn('memberships.classroom_id', $classroomIds)
                ->where('memberships.status', 'active')
                ->where('users.status', 'active')
                ->whereNull('users.deleted_at')
                ->distinct()
                ->count('memberships.student_id')
            : 0;

        $classroomRosterPreviews = $this->classroomRosterPreviews($classrooms);

        if ($courseIds->isEmpty()) {
            return $this->emptyState($teacher, $classrooms, $studentCount, $classroomRosterPreviews);
        }

        $weekStart = now()->startOfWeek(Carbon::SATURDAY);
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY);
        // Shared-course eligibility is resolved once and reused by all progress queries.
        $exclusiveCourseIds = $this->exclusiveCourseIdsForTeacher($teacher->id, $courseIds);
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
            ->whereIn('status', ['scheduled', 'live'])
            ->where(function ($window): void {
                $window->whereBetween('scheduled_at', [now(), now()->addDays(7)])
                    ->orWhere(function ($running): void {
                        $running->where('status', 'live')
                            ->where('scheduled_at', '<=', now())
                            ->where(function ($end): void {
                                $end->where('scheduled_end_at', '>=', now())
                                    ->orWhere(function ($legacy): void {
                                        // Legacy rows without an end time use the model's max allowed duration.
                                        $legacy->whereNull('scheduled_end_at')
                                            ->where('scheduled_at', '>=', now()->subHours(24));
                                    });
                            });
                    });
            })
            ->with([
                'course:id,title',
                'classroom:id,title',
            ])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(function (LiveClass $liveClass): LiveClass {
                $liveClass->setAttribute(
                    'can_join',
                    app(MeetingLinkService::class)->urlMatchesProvider(
                        (string) $liveClass->provider,
                        (string) $liveClass->meeting_url,
                    ),
                );

                return $liveClass;
            });

        $weekChart = $this->weeklyChart($teacher->id, $courseIds, $exclusiveCourseIds, $weekStart, $weekEnd);

        $chart = [
            'week' => $weekChart,
            'month' => $this->monthlyChart($teacher->id, $courseIds, $exclusiveCourseIds),
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

        $courseProgress = $this->courseProgress($teacher->id, $courseIds, $exclusiveCourseIds);
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
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->where('classrooms.status', 'active')
            ->whereIn('classrooms.academy_id', $activeAcademyIds)
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


        $avatarUsers = User::query()
            ->whereIn('id', $topStudents->pluck('id')->all())
            ->with(['studentProfile.media' => fn ($query) => $query
                ->wherePivot('collection', 'student-avatar')
                ->orderByPivot('sort_order')])
            ->get(['id'])
            ->keyBy('id');

        $topStudents->each(function ($row) use ($avatarUsers): void {
            $avatar = $avatarUsers->get((int) $row->id)?->studentProfile?->media?->first();
            $row->avatar_url = $avatar && $avatar->visibility === 'public' ? $avatar->url() : null;
        });

        return [
            'teacher' => $teacher,
            'dashboardDate' => PersianUi::date(now()),
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
            'classroomRosterPreviews' => $classroomRosterPreviews,
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
    private function teacherLearningMatrix(int $teacherId, $courseIds, $exclusiveCourseIds)
    {

        return DB::table('course_teacher as course_teachers')
            ->join('courses', 'courses.id', '=', 'course_teachers.course_id')
            ->join('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->join('users as learners', function ($join): void {
                $join->on('learners.id', '=', 'enrollments.student_id')
                    ->where('learners.status', '=', 'active')
                    ->whereNull('learners.deleted_at');
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

    private function courseProgress(int $teacherId, $courseIds, $exclusiveCourseIds)
    {
        return $this->teacherLearningMatrix($teacherId, $courseIds, $exclusiveCourseIds)
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

    private function weeklyChart(int $teacherId, $courseIds, $exclusiveCourseIds, Carbon $from, Carbon $to): array
    {
        $labels = [];
        $values = [];
        $dayMap = [0 => 'ی', 1 => 'د', 2 => 'س', 3 => 'چ', 4 => 'پ', 5 => 'ج', 6 => 'ش'];

        $rows = $this->teacherLearningMatrix($teacherId, $courseIds, $exclusiveCourseIds)
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

    private function monthlyChart(int $teacherId, $courseIds, $exclusiveCourseIds): array
    {
        $from = now()->subDays(30)->startOfDay();
        $to = now()->endOfDay();

        // Aggregate one row per date in SQL. Seven bounded 5-day buckets avoid
        // week-number/year-boundary differences between MySQL, SQLite and PostgreSQL.
        $dailyValues = $this->teacherLearningMatrix($teacherId, $courseIds, $exclusiveCourseIds)
            ->whereNotNull('progress.last_watched_at')
            ->whereBetween('progress.last_watched_at', [$from, $to])
            ->groupBy(DB::raw('DATE(progress.last_watched_at)'))
            ->selectRaw('DATE(progress.last_watched_at) as day, AVG(progress.progress_percent) as value')
            ->pluck('value', 'day');

        $periods = array_fill(0, 7, []);
        foreach ($dailyValues as $day => $value) {
            $date = Carbon::parse($day)->startOfDay();
            $offset = min(6, intdiv((int) max(0, $from->diffInDays($date)), 5));
            $periods[$offset][] = (float) $value;
        }

        $labels = [];
        $values = [];
        for ($index = 0; $index < 7; $index++) {
            $periodStart = $from->copy()->addDays($index * 5);
            $labels[] = $this->faDigits('بازه ' . ($index + 1));
            $values[] = $periods[$index] === []
                ? 0
                : (int) round(array_sum($periods[$index]) / count($periods[$index]));
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function todaySessions(User $teacher, $classrooms, $courseIds)
    {
        $weekday = now()->dayOfWeek;
        $meetingLinks = app(MeetingLinkService::class);

        $scheduled = $classrooms->flatMap(function ($classroom) use ($weekday, $meetingLinks) {
            return $classroom->schedules
                ->where('weekday', $weekday)
                ->map(function ($schedule) use ($classroom, $meetingLinks) {
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
                        'meeting_url' => $schedule->meeting_url,
                        'can_join' => $status !== 'پایان یافته'
                            && $meetingLinks->urlMatchesProvider(
                                (string) $schedule->provider,
                                (string) $schedule->meeting_url,
                            ),
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
            ->map(function (LiveClass $item) use ($meetingLinks) {
                $scheduledEnd = $item->scheduled_end_at
                    ?? $item->scheduled_at->copy()->addMinutes(max(1, (int) ($item->duration_minutes ?? 60)));
                $ended = $item->ended_at !== null || $scheduledEnd->isPast();
                $status = $ended
                    ? 'پایان یافته'
                    : ($item->scheduled_at->isPast() ? 'در حال برگزاری' : 'شروع نشده');

                return [
                    'time' => $this->faDigits($item->scheduled_at->format('H:i')),
                    'title' => $item->title,
                    'meta' => $item->classroom?->title ?? $item->course?->title,
                    'status' => $status,
                    'type' => 'online',
                    'meeting_url' => $item->meeting_url,
                    'can_join' => $status !== 'پایان یافته'
                        && $meetingLinks->urlMatchesProvider(
                            (string) $item->provider,
                            (string) $item->meeting_url,
                        ),
                ];
            });

        return $scheduled->concat($live)->sortBy('time')->values();
    }


    /**
     * Show a small, accurate roster preview for the teacher's real assigned classes.
     * Only active classroom membership + active enrollment rows are eligible.
     * Student profile photos are loaded in a bounded eager-load query, never as demo URLs.
     */
    private function classroomRosterPreviews($classrooms)
    {
        return $classrooms->take(4)->map(function ($classroom): array {
            $roster = $classroom->students()
                ->wherePivot('status', 'active')
                ->where('users.status', 'active')
                ->whereNull('users.deleted_at')
                ->whereHas('enrollments', fn ($query) => $query
                    ->where('course_id', $classroom->course_id)
                    ->where('classroom_id', $classroom->id)
                    ->where('status', 'active'));

            $total = (clone $roster)->count();
            $students = $roster
                ->with(['studentProfile.media' => fn ($query) => $query
                    ->wherePivot('collection', 'student-avatar')
                    ->orderByPivot('sort_order')])
                ->orderBy('users.name')
                ->limit(4)
                ->get(['users.id', 'users.name']);

            $students->each(function (User $student): void {
                $avatar = $student->studentProfile?->media?->first();
                $student->setAttribute(
                    'avatar_url',
                    $avatar && $avatar->visibility === 'public' ? $avatar->url() : null,
                );
            });

            return [
                'classroom_id' => (int) $classroom->id,
                'title' => (string) $classroom->title,
                'course_title' => (string) ($classroom->course?->title ?? 'بدون دوره'),
                'student_count' => $total,
                'students' => $students,
                'url' => route('teacher.classrooms.index'),
            ];
        })->values();
    }

    private function emptyState(User $teacher, $classrooms, int $studentCount, $classroomRosterPreviews = null): array
    {
        return [
            'teacher' => $teacher,
            'dashboardDate' => PersianUi::date(now()),
            'completedSessions' => 0,
            'metrics' => [
                'activeClasses' => $classrooms->count(),
                'studentCount' => $studentCount,
                'weeklySessions' => 0,
                'pendingAssignmentReviews' => 0,
                'pendingExamReviews' => 0,
                'pendingReviews' => 0,
            ],
            'todaySessions' => $this->todaySessions($teacher, $classrooms, collect()),
            'upcomingClasses' => collect(),
            'chart' => [
                'week' => ['labels' => [], 'values' => []],
                'month' => ['labels' => [], 'values' => []],
            ],
            'activities' => collect(),
            'courseProgress' => collect(),
            'topStudents' => collect(),
            'classroomRosterPreviews' => $classroomRosterPreviews ?? $this->classroomRosterPreviews($classrooms),
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
