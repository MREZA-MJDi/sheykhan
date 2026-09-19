<?php

namespace App\Services;

use App\Models\Academy;
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
                    'pendingExams' => 0,
                    'activeEnrollments' => 0,
                ],
                'teacherReports' => collect(),
                'recentCourses' => collect(),
                'courseReports' => collect(),
                'recentEnrollments' => collect(),
                'upcomingLiveClasses' => collect(),
            ];
        }

        $academies = Academy::query()
            ->whereIn('id', $academyIds)
            ->withCount([
                'courses as course_count',
                'users as member_count',
                'users as teacher_count' => fn ($query) => $query
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active'),
                'users as student_count' => fn ($query) => $query
                    ->where('academy_user.role', 'student')
                    ->where('academy_user.status', 'active'),
            ])
            ->orderBy('name')
            ->get();

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
            'activeEnrollments' => DB::table('course_enrollments')
                ->whereIn('course_id', $courseIds)
                ->where('status', 'active')
                ->count(),
            'pendingReviews' => DB::table('assignment_submissions as submissions')
                ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                ->whereIn('assignments.course_id', $courseIds)
                ->whereNotNull('submissions.submitted_at')
                ->whereNull('submissions.graded_at')
                ->count(),
            'pendingExams' => DB::table('exam_attempts as attempts')
                ->join('exams', 'exams.id', '=', 'attempts.exam_id')
                ->whereIn('exams.course_id', $courseIds)
                ->where('attempts.status', 'needs_review')
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
            'academies' => $academies,
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
            'courseReports' => $this->courseReports($courseIds),
            'recentEnrollments' => $this->recentEnrollments($courseIds),
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

    private function courseReports($courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        $sales = DB::table('course_enrollments')
            ->whereIn('course_id', $courseIds)
            ->where('status', 'active')
            ->groupBy('course_id')
            ->select('course_id', DB::raw('COALESCE(SUM(paid_amount), 0) AS sales'))
            ->pluck('sales', 'course_id');

        return Course::query()
            ->whereIn('id', $courseIds)
            ->with('teachers:id,name')
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (Course $course) use ($sales) {
                $course->setAttribute('sales', (float) ($sales[$course->id] ?? 0));

                return $course;
            });
    }

    private function recentEnrollments($courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('course_enrollments as enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->join('users as students', 'students.id', '=', 'enrollments.student_id')
            ->whereIn('enrollments.course_id', $courseIds)
            ->orderByDesc('enrollments.created_at')
            ->limit(8)
            ->get([
                'enrollments.id',
                'enrollments.status',
                'enrollments.paid_amount',
                'enrollments.created_at',
                'courses.title as course_title',
                'students.name as student_name',
            ]);
    }
}
