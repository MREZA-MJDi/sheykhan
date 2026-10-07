<?php

namespace App\Services;

use App\Models\LiveClass;
use App\Models\User;
use App\Support\PersianUi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class StudentDashboardService
{
    public function build(User $student): array
    {
        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->with([
                'course:id,academy_id,title,slug,level,access_type,price',
                'course.academy:id,name',
            ])
            ->latest('started_at')
            ->get();

        $courseIds = $enrollments->pluck('course_id');

        $progress = $this->progressByCourse($student->id, $courseIds);

        $courses = $enrollments->map(function ($enrollment) use ($progress) {
            $item = $enrollment->course;
            $item->learning_progress = (float) ($progress[$item->id] ?? 0);

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
                ]);

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
                        'lamp' => $recordingReady ? 'روشن' : 'خاموش',
                        'available' => $recordingReady,
                        'href' => $recordingReady ? route('media.view', $session->recording) : null,
                    ];
                });

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('submissions.student_id', $student->id)
            ->whereNotNull('submissions.graded_at')
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
            ->whereNotNull('attempts.submitted_at')
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

        $overallProgress = (float) ($progress->avg() ?? 0);

        return [
            'student' => $student,
            'profile' => $student->studentProfile,
            'courses' => $courses,
            'overallProgress' => round($overallProgress),
            'activeCourseCount' => $courses->count(),
            'pendingAssignments' => $pendingAssignments,
            'upcomingLiveClasses' => $upcomingLive,
            'assignments' => $assignmentItems,
            'recentResults' => $recentResults,
            'resources' => $resources,
            'sessions' => $sessions,
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
            ->select('course_sections.course_id')
            ->selectRaw('AVG(progress.progress_percent) as progress_average')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->course_id => (float) $row->progress_average,
            ]);
    }
}
