<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class OwnerDashboardService
{
    public function build(User $owner): array
    {
        $academyIds = DB::table('academies')
            ->where('owner_id', $owner->id)
            ->where('status', 'active')
            ->pluck('id');

        if ($academyIds->isEmpty()) {
            return [
                'owner' => $owner,
                'academies' => collect(),
                'metrics' => [
                    'courses' => 0,
                    'teachers' => 0,
                    'students' => 0,
                    'classrooms' => 0,
                    'sales' => 0,
                    'published' => 0,
                    'pendingReviews' => 0,
                ],
                'teacherReports' => collect(),
                'recentCourses' => collect(),
                'upcomingLiveClasses' => collect(),
            ];
        }

        $courseIds = Course::query()
            ->whereIn('academy_id', $academyIds)
            ->pluck('id');

        $teacherIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id');

        $studentIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id');

        $metrics = [
            'courses' => $courseIds->count(),
            'teachers' => $teacherIds->count(),
            'students' => $studentIds->count(),
            'classrooms' => DB::table('classrooms')
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active')
                ->count(),
            'sales' => (float) DB::table('course_enrollments')
                ->whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->sum('paid_amount'),
            'published' => Course::query()
                ->whereIn('id', $courseIds)
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'pendingReviews' => DB::table('assignment_submissions as submissions')
                ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                ->whereIn('assignments.course_id', $courseIds)
                ->whereNotNull('submissions.submitted_at')
                ->whereNull('submissions.graded_at')
                ->count(),
        ];

        $teacherReports = DB::table('course_teacher as ct')
            ->join('users', 'users.id', '=', 'ct.teacher_id')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->leftJoin('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->whereIn('courses.academy_id', $academyIds)
            ->whereIn('ct.teacher_id', $teacherIds)
            ->groupBy('ct.teacher_id', 'users.name')
            ->select([
                'ct.teacher_id',
                'users.name',
                DB::raw('COUNT(DISTINCT courses.id) AS course_count'),
                DB::raw('COUNT(DISTINCT enrollments.student_id) AS student_count'),
                DB::raw('COALESCE(SUM(enrollments.paid_amount), 0) AS sales'),
            ])
            ->orderByDesc('student_count')
            ->get();

        $teacherProgress = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->join('course_teacher as ct', 'ct.course_id', '=', 'course_sections.course_id')
            ->whereIn('course_sections.course_id', $courseIds)
            ->whereIn('ct.teacher_id', $teacherIds)
            ->groupBy('ct.teacher_id')
            ->select('ct.teacher_id', DB::raw('ROUND(AVG(progress.progress_percent), 1) AS progress_average'))
            ->pluck('progress_average', 'ct.teacher_id');

        $teacherPending = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.course_id', $courseIds)
            ->whereIn('assignments.teacher_id', $teacherIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->groupBy('assignments.teacher_id')
            ->select('assignments.teacher_id', DB::raw('COUNT(*) AS pending_reviews'))
            ->pluck('pending_reviews', 'assignments.teacher_id');

        $teacherReports = $teacherReports->map(function ($report) use ($teacherProgress, $teacherPending) {
            $report->progress_average = (float) ($teacherProgress[$report->teacher_id] ?? 0);
            $report->pending_reviews = (int) ($teacherPending[$report->teacher_id] ?? 0);

            return $report;
        });

        return [
            'owner' => $owner,
            'academies' => $academyIds,
            'metrics' => $metrics,
            'teacherReports' => $teacherReports,
            'recentCourses' => Course::query()
                ->whereIn('id', $courseIds)
                ->with('academy:id,name')
                ->withCount([
                    'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                ])
                ->latest()
                ->limit(6)
                ->get(),
            'upcomingLiveClasses' => DB::table('live_classes')
                ->join('courses', 'courses.id', '=', 'live_classes.course_id')
                ->leftJoin('classrooms', 'classrooms.id', '=', 'live_classes.classroom_id')
                ->leftJoin('users as teachers', 'teachers.id', '=', 'live_classes.teacher_id')
                ->whereIn('live_classes.course_id', $courseIds)
                ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
                ->where('live_classes.status', 'scheduled')
                ->orderBy('live_classes.scheduled_at')
                ->limit(8)
                ->get([
                    'live_classes.id',
                    'live_classes.title',
                    'live_classes.scheduled_at',
                    'courses.title as course_title',
                    'classrooms.title as classroom_title',
                    'teachers.name as teacher_name',
                ]),
        ];
    }
}
