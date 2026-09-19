<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class TeacherDashboardService
{
    public function build(User $teacher): array
    {
        $courseIds = $teacher->taughtCourses()->pluck('courses.id');
        $classrooms = $teacher->classroomsAsTeacher()
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
        $monthStart = now()->startOfMonth();

        $weeklyProgress = $this->progressAverage($teacher->id, $courseIds);
        $pendingAssignmentReviews = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->whereHas('submissions', fn ($query) => $query
                ->whereNotNull('submitted_at')
                ->whereNull('graded_at'))
            ->count();

        $pendingExamReviews = ExamAttempt::query()
            ->where('status', 'submitted')
            ->whereHas('exam', fn ($query) => $query->where('teacher_id', $teacher->id))
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
            ->where('scheduled_at', '<', now())
            ->where('status', '!=', 'cancelled')
            ->count();

        $monthlySales = (float) DB::table('course_enrollments')
            ->whereIn('course_id', $courseIds)
            ->where('status', 'active')
            ->where('created_at', '>=', $monthStart)
            ->sum('paid_amount');

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

        $chart = [
            ...$this->weeklyChart($teacher->id, $courseIds, $weekStart, $weekEnd),
            'month' => $this->monthlyChart($teacher->id, $courseIds),
        ];

        $activities = Assignment::query()
            ->where('teacher_id', $teacher->id)
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

        return [
            'teacher' => $teacher,
            'profile' => $teacher->teacherProfile,
            'dashboardDate' => $this->faDigits(now()->format('Y/m/d')),
            'weeklyProgress' => round((float) $weeklyProgress),
            'completedSessions' => $completedSessions,
            'metrics' => [
                'activeClasses' => count($classroomIds),
                'studentCount' => $studentCount,
                'weeklySessions' => $weeklySessions,
                'monthlySales' => $monthlySales,
                'pendingReviews' => $pendingAssignmentReviews + $pendingExamReviews,
            ],
            'todaySessions' => $todaySessions,
            'upcomingClasses' => $upcomingClasses,
            'chart' => $chart,
            'activities' => $activities,
            'courseProgress' => $courseProgress,
            'calendarEvents' => $this->calendarEvents($teacher, $courseIds),
        ];
    }

    private function progressAverage(int $teacherId, $courseIds): float
    {
        return (float) (
            DB::table('lesson_progress as progress')
                ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
                ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
                ->join('course_enrollments as enrollments', function ($join): void {
                    $join->on('enrollments.course_id', '=', 'course_sections.course_id')
                        ->on('enrollments.student_id', '=', 'progress.user_id')
                        ->where('enrollments.status', '=', 'active');
                })
                ->join('course_teacher as ct', function ($join) use ($teacherId): void {
                    $join->on('ct.course_id', '=', 'course_sections.course_id')
                        ->where('ct.teacher_id', '=', $teacherId);
                })
                ->whereIn('course_sections.course_id', $courseIds)
                ->avg('progress.progress_percent') ?? 0
        );
    }

    private function courseProgress(int $teacherId, $courseIds)
    {
        return DB::table('course_teacher as ct')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->leftJoin('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->leftJoin('lesson_progress as progress', 'progress.user_id', '=', 'enrollments.student_id')
            ->leftJoin('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->leftJoin('course_sections', function ($join): void {
                $join->on('course_sections.id', '=', 'lessons.course_section_id')
                    ->on('course_sections.course_id', '=', 'courses.id');
            })
            ->where('ct.teacher_id', $teacherId)
            ->whereIn('ct.course_id', $courseIds)
            ->groupBy('ct.course_id', 'courses.title')
            ->orderByDesc('ct.course_id')
            ->get([
                'ct.course_id',
                'courses.title',
                DB::raw('COUNT(DISTINCT enrollments.student_id) AS student_count'),
                DB::raw('COALESCE(AVG(progress.progress_percent), 0) AS progress_average'),
            ])
            ->map(function ($row) {
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

        $rows = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->join('course_teacher as ct', function ($join) use ($teacherId): void {
                $join->on('ct.course_id', '=', 'course_sections.course_id')
                    ->where('ct.teacher_id', '=', $teacherId);
            })
            ->whereIn('course_sections.course_id', $courseIds)
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

        $rows = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->join('course_teacher as ct', function ($join) use ($teacherId): void {
                $join->on('ct.course_id', '=', 'course_sections.course_id')
                    ->where('ct.teacher_id', '=', $teacherId);
            })
            ->whereIn('course_sections.course_id', $courseIds)
            ->whereBetween('progress.last_watched_at', [$from, $to])
            ->groupBy(DB::raw('WEEK(progress.last_watched_at)'))
            ->selectRaw('WEEK(progress.last_watched_at) as week_number, AVG(progress.progress_percent) as value')
            ->pluck('value', 'week_number');

        $labels = [];
        $values = [];

        for ($index = 3; $index >= 0; $index--) {
            $date = now()->subWeeks($index);
            $week = (int) $date->format('W');
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

                    $status = $now->between(
                        now()->copy()->setTimeFrom($start),
                        now()->copy()->setTimeFrom($end)
                    ) ? 'در حال برگزاری' : 'شروع نشده';

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
                return [
                    'time' => $this->faDigits($item->scheduled_at->format('H:i')),
                    'title' => $item->title,
                    'meta' => $item->classroom?->title ?? $item->course?->title,
                    'status' => $item->scheduled_at->isPast() ? 'در حال برگزاری' : 'شروع نشده',
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
            'dashboardDate' => $this->faDigits(now()->format('Y/m/d')),
            'weeklyProgress' => 0,
            'completedSessions' => 0,
            'metrics' => [
                'activeClasses' => $classrooms->count(),
                'studentCount' => $studentCount,
                'weeklySessions' => 0,
                'monthlySales' => 0,
                'pendingReviews' => 0,
            ],
            'todaySessions' => collect(),
            'upcomingClasses' => collect(),
            'chart' => ['labels' => [], 'values' => []],
            'activities' => collect(),
            'courseProgress' => collect(),
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
