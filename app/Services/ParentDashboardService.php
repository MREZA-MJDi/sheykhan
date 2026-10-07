<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

        $progress = DB::table('lesson_progress')
            ->whereIn('user_id', $childIds)
            ->groupBy('user_id')
            ->select('user_id')
            ->selectRaw('AVG(progress_percent) as progress_average')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->user_id => (float) $row->progress_average]);

        $enrollmentCounts = DB::table('course_enrollments')
            ->whereIn('student_id', $childIds)
            ->where('status', 'active')
            ->groupBy('student_id')
            ->pluck(DB::raw('COUNT(*)'), 'student_id');

        $pendingAssignments = DB::table('users as students')
            ->whereIn('students.id', $childIds)
            ->select('students.id')
            ->selectSub(function ($query): void {
                $query->from('assignments')
                    ->where('assignments.status', 'published')
                    ->whereExists(function ($enrollments): void {
                        $enrollments->selectRaw('1')
                            ->from('course_enrollments')
                            ->whereColumn('course_enrollments.student_id', 'students.id')
                            ->whereColumn('course_enrollments.course_id', 'assignments.course_id')
                            ->where('course_enrollments.status', 'active')
                            ->where(function ($paid): void {
                                $paid->where('course_enrollments.paid_amount', '>', 0)
                                    ->orWhereExists(function ($free): void {
                                        $free->selectRaw('1')
                                            ->from('courses')
                                            ->whereColumn('courses.id', 'assignments.course_id')
                                            ->where('courses.access_type', 'free');
                                    });
                            });
                    })
                    ->where(function ($classroom): void {
                        $classroom->whereNull('assignments.classroom_id')
                            ->orWhereExists(function ($membership): void {
                                $membership->selectRaw('1')
                                    ->from('classroom_student')
                                    ->whereColumn('classroom_student.classroom_id', 'assignments.classroom_id')
                                    ->whereColumn('classroom_student.student_id', 'students.id')
                                    ->where('classroom_student.status', 'active');
                            });
                    })
                    ->whereNotExists(function ($submissions): void {
                        $submissions->selectRaw('1')
                            ->from('assignment_submissions')
                            ->whereColumn('assignment_submissions.assignment_id', 'assignments.id')
                            ->whereColumn('assignment_submissions.student_id', 'students.id')
                            ->whereNotNull('assignment_submissions.submitted_at');
                    })
                    ->selectRaw('COUNT(*)');
            }, 'pending_count')
            ->pluck('pending_count', 'students.id');

        $children = $children->map(function (User $child) use ($progress, $enrollmentCounts, $pendingAssignments) {
            $child->dashboard_progress = round((float) ($progress[$child->id] ?? 0));
            $child->dashboard_courses = (int) ($enrollmentCounts[$child->id] ?? 0);
            $child->dashboard_pending = (int) ($pendingAssignments[$child->id] ?? 0);

            return $child;
        });

        $upcomingLiveClasses = DB::table('live_classes')
            ->join('courses', 'courses.id', '=', 'live_classes.course_id')
            ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
            ->where('live_classes.status', 'scheduled')
            ->whereExists(function ($query) use ($childIds): void {
                $query->selectRaw('1')
                    ->from('course_enrollments')
                    ->whereIn('course_enrollments.student_id', $childIds)
                    ->where('course_enrollments.status', 'active')
                    ->whereColumn('course_enrollments.course_id', 'live_classes.course_id')
                    ->where(function ($scope): void {
                        $scope->where('course_enrollments.paid_amount', '>', 0)
                            ->orWhereExists(function ($free): void {
                                $free->selectRaw('1')
                                    ->from('courses')
                                    ->whereColumn('courses.id', 'live_classes.course_id')
                                    ->where('courses.access_type', 'free');
                            });
                    })
                    ->where(function ($classroomQuery): void {
                        $classroomQuery
                            ->whereNull('live_classes.classroom_id')
                            ->orWhereColumn('course_enrollments.classroom_id', 'live_classes.classroom_id');
                    });
            })
            ->select([
                'live_classes.id',
                'live_classes.title',
                'live_classes.scheduled_at',
                'live_classes.course_id',
                'courses.title as course_title',
            ])
            ->selectSub(function ($query) use ($childIds): void {
                $query->from('course_enrollments')
                    ->whereIn('student_id', $childIds)
                    ->where('status', 'active')
                    ->whereColumn('course_id', 'live_classes.course_id')
                    ->where(function ($scope): void {
                        $scope->where('paid_amount', '>', 0)
                            ->orWhereExists(function ($free): void {
                                $free->selectRaw('1')
                                    ->from('courses')
                                    ->whereColumn('courses.id', 'live_classes.course_id')
                                    ->where('courses.access_type', 'free');
                            });
                    })
                    ->where(function ($classroom): void {
                        $classroom->whereNull('live_classes.classroom_id')
                            ->orWhereColumn('course_enrollments.classroom_id', 'live_classes.classroom_id');
                    })
                    ->orderBy('student_id')
                    ->limit(1)
                    ->select('student_id');
            }, 'student_id')
            ->orderBy('live_classes.scheduled_at')
            ->limit(8)
            ->get();

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
            'overallProgress' => round((float) $progress->avg() ),
            'upcomingLiveClasses' => $upcomingLiveClasses->map(function ($item) {
                $item->scheduled_at = $item->scheduled_at instanceof \Carbon\CarbonInterface
                    ? $item->scheduled_at
                    : Carbon::parse($item->scheduled_at);
                return $item;
            }),
            'recentResults' => $recentResults->map(function ($item) {
                $item->graded_at = $item->graded_at instanceof \Carbon\CarbonInterface
                    ? $item->graded_at
                    : Carbon::parse($item->graded_at);
                return $item;
            }),
        ];
    }
}
