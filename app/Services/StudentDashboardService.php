<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StudentDashboardService
{
    public function build(User $student): array
    {
        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->with('course:id,title,slug,level,access_type,price')
            ->latest('started_at')
            ->get();

        $courseIds = $enrollments->pluck('course_id');

        $progress = $this->progressByCourse($student->id, $courseIds);

        $courses = $enrollments->map(function ($enrollment) use ($progress) {
            $item = $enrollment->course;
            $item->learning_progress = (float) ($progress[$item->id] ?? 0);

            return $item;
        });

        $assignmentItems = $courseIds->isEmpty()
            ? collect()
            : DB::table('assignments')
                ->leftJoin('assignment_submissions as submissions', function ($join) use ($student): void {
                    $join->on('submissions.assignment_id', '=', 'assignments.id')
                        ->where('submissions.student_id', '=', $student->id);
                })
                ->whereIn('assignments.course_id', $courseIds)
                ->where('assignments.status', 'published')
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
                ]);

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
                ]);

        $recentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('submissions.student_id', $student->id)
            ->whereNotNull('submissions.graded_at')
            ->orderByDesc('submissions.graded_at')
            ->limit(5)
            ->get([
                'assignments.title',
                'submissions.score',
                'submissions.graded_at',
            ]);

        $overallProgress = (float) ($progress->avg() ?? 0);

        return [
            'student' => $student,
            'profile' => $student->studentProfile,
            'courses' => $courses,
            'overallProgress' => round($overallProgress),
            'activeCourseCount' => $courses->count(),
            'pendingAssignments' => $assignmentItems->whereNull('submitted_at')->count(),
            'upcomingLiveClasses' => $upcomingLive,
            'assignments' => $assignmentItems,
            'recentResults' => $recentResults,
        ];
    }

    private function progressByCourse(int $studentId, $courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('progress.user_id', $studentId)
            ->whereIn('course_sections.course_id', $courseIds)
            ->groupBy('course_sections.course_id')
            ->pluck(DB::raw('AVG(progress.progress_percent)'), 'course_sections.course_id');
    }
}
