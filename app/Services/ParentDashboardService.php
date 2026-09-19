<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ParentDashboardService
{
    public function build(User $parent): array
    {
        $children = $parent->children()
            ->with('studentProfile')
            ->orderBy('users.name')
            ->get();

        if ($children->isEmpty()) {
            return [
                'parent' => $parent,
                'children' => collect(),
                'overallProgress' => 0,
                'upcomingLiveClasses' => collect(),
                'recentResults' => collect(),
            ];
        }

        $childIds = $children->pluck('id');

        $progress = DB::table('lesson_progress as progress')
            ->join('users', 'users.id', '=', 'progress.user_id')
            ->whereIn('progress.user_id', $childIds)
            ->groupBy('progress.user_id')
            ->pluck(DB::raw('AVG(progress.progress_percent)'), 'progress.user_id');

        $enrollmentCounts = DB::table('course_enrollments')
            ->whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->groupBy('student_id')
            ->pluck(DB::raw('COUNT(*)'), 'student_id');

        $pendingAssignments = DB::table('assignments')
            ->join('course_enrollments', 'course_enrollments.course_id', '=', 'assignments.course_id')
            ->leftJoin('assignment_submissions as submissions', function ($join): void {
                $join->on('submissions.assignment_id', '=', 'assignments.id')
                    ->on('submissions.student_id', '=', 'course_enrollments.student_id');
            })
            ->whereIn('course_enrollments.student_id', $childIds)
            ->where('course_enrollments.status', 'active')
            ->where('assignments.status', 'published')
            ->whereNull('submissions.submitted_at')
            ->groupBy('course_enrollments.student_id')
            ->pluck(DB::raw('COUNT(DISTINCT assignments.id)'), 'course_enrollments.student_id');

        $children = $children->map(function (User $child) use ($progress, $enrollmentCounts, $pendingAssignments) {
            $child->dashboard_progress = round((float) ($progress[$child->id] ?? 0));
            $child->dashboard_courses = (int) ($enrollmentCounts[$child->id] ?? 0);
            $child->dashboard_pending = (int) ($pendingAssignments[$child->id] ?? 0);

            return $child;
        });

        $upcomingLiveClasses = DB::table('live_classes')
            ->join('course_enrollments', 'course_enrollments.course_id', '=', 'live_classes.course_id')
            ->join('courses', 'courses.id', '=', 'live_classes.course_id')
            ->whereIn('course_enrollments.student_id', $childIds)
            ->where('course_enrollments.status', 'active')
            ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
            ->where('live_classes.status', 'scheduled')
            ->orderBy('live_classes.scheduled_at')
            ->limit(8)
            ->get([
                'live_classes.id',
                'live_classes.title',
                'live_classes.scheduled_at',
                'courses.title as course_title',
                'course_enrollments.student_id',
            ]);

        $recentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('users', 'users.id', '=', 'submissions.student_id')
            ->whereIn('submissions.student_id', $childIds)
            ->whereNotNull('submissions.graded_at')
            ->orderByDesc('submissions.graded_at')
            ->limit(8)
            ->get([
                'users.name as student_name',
                'assignments.title',
                'submissions.score',
                'submissions.graded_at',
            ]);

        return [
            'parent' => $parent,
            'children' => $children,
            'overallProgress' => round((float) ($progress->avg() ?? 0)),
            'upcomingLiveClasses' => $upcomingLiveClasses,
            'recentResults' => $recentResults,
        ];
    }
}
