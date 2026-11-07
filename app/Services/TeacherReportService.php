<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Models\User;
use App\Support\PersianUi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TeacherReportService
{
    public function build(User $teacher): array
    {
        $activeAcademyIds = $teacher->academies()
            ->where('academies.status', 'active')
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        $courseBaseQuery = $teacher->taughtCourses()
            ->whereIn('courses.academy_id', $activeAcademyIds);
        $courseCount = (clone $courseBaseQuery)->count();
        $courseIds = (clone $courseBaseQuery)->select('courses.id');

        $courses = (clone $courseBaseQuery)
            ->withCount([
                'sections',
                'lessons' => fn ($q) => $q->where('lessons.status', 'published'),
                'assignments' => fn ($q) => $q->where('assignments.teacher_id', $teacher->id),
                'exams' => fn ($q) => $q->where('exams.teacher_id', $teacher->id),
                'liveClasses' => fn ($q) => $q->where('live_classes.teacher_id', $teacher->id),
            ])
            ->orderBy('courses.title')
            ->orderBy('courses.id')
            ->paginate(20, ['courses.*'], 'courses_page')
            ->withQueryString();

        $classroomBaseQuery = $teacher->classroomsAsTeacher()
            ->whereIn('classrooms.academy_id', $activeAcademyIds)
            ->where('classrooms.status', 'active');
        $classroomCount = (clone $classroomBaseQuery)->count('classrooms.id');
        $classroomIds = (clone $classroomBaseQuery)->select('classrooms.id');

        $classrooms = (clone $classroomBaseQuery)
            ->with(['course:id,title'])
            ->orderBy('classrooms.title')
            ->orderBy('classrooms.id')
            ->paginate(12, ['classrooms.*'], 'classrooms_page')
            ->withQueryString();
        $classroomPageIds = $classrooms->getCollection()->modelKeys();

        $studentCount = $classroomCount > 0
            ? DB::table('classroom_student as memberships')
                ->join('classrooms', 'classrooms.id', '=', 'memberships.classroom_id')
                ->join('users', 'users.id', '=', 'memberships.student_id')
                ->join('course_enrollments as enrollments', function ($join): void {
                    $join->on('enrollments.student_id', '=', 'memberships.student_id')
                        ->on('enrollments.classroom_id', '=', 'classrooms.id')
                        ->on('enrollments.course_id', '=', 'classrooms.course_id')
                        ->where('enrollments.status', '=', 'active');
                })
                ->whereIn('memberships.classroom_id', $classroomIds)
                ->where('memberships.status', 'active')
                ->where('users.status', 'active')
                ->whereNull('users.deleted_at')
                ->distinct()
                ->count('memberships.student_id')
            : 0;

        $exclusiveCourseIds = $this->exclusiveCourseIdsForTeacher($teacher->id, $courseIds);

        $pendingAssignmentReviews = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('assignments.teacher_id', $teacher->id)
            ->whereIn('assignments.course_id', $courseIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->count();

        $gradedAssignmentSubmissions = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('assignments.teacher_id', $teacher->id)
            ->whereIn('assignments.course_id', $courseIds)
            ->whereNotNull('submissions.graded_at')
            ->count();

        $pendingExamReviews = ExamAttempt::query()
            ->whereIn('status', ['submitted', 'pending_review', 'needs_review'])
            ->whereHas('exam', fn ($query) => $query
                ->where('teacher_id', $teacher->id)
                ->whereIn('course_id', $courseIds))
            ->count();

        $gradedExamAttempts = ExamAttempt::query()
            ->where('status', 'graded')
            ->whereHas('exam', fn ($query) => $query
                ->where('teacher_id', $teacher->id)
                ->whereIn('course_id', $courseIds))
            ->count();

        $examAverage = ExamAttempt::query()
            ->whereNotNull('score')
            ->whereHas('exam', fn ($query) => $query
                ->where('teacher_id', $teacher->id)
                ->whereIn('course_id', $courseIds))
            ->avg('score');

        $completedLiveClassesCount = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->whereNotNull('ended_at')
            ->where('status', '!=', 'cancelled')
            ->count();

        $totalLiveClassesCount = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->where('teacher_id', $teacher->id)
            ->where('status', '!=', 'cancelled')
            ->count();

        $attendanceTotals = $classroomCount > 0
            ? DB::table('attendances')
                ->whereIn('classroom_id', $classroomIds)
                ->whereNotNull('scheduled_minutes')
                ->whereNotNull('attended_minutes')
                ->where('scheduled_minutes', '>', 0)
                ->whereIn('status', ['present', 'late', 'absent'])
                ->selectRaw('COALESCE(SUM(scheduled_minutes), 0) AS scheduled_minutes')
                ->selectRaw('COALESCE(SUM(attended_minutes), 0) AS attended_minutes')
                ->selectRaw('COUNT(*) AS measured_sessions')
                ->first()
            : null;

        $totalScheduledMinutes = (int) ($attendanceTotals->scheduled_minutes ?? 0);
        $totalAttendedMinutes = min($totalScheduledMinutes, (int) ($attendanceTotals->attended_minutes ?? 0));
        $overallAttendanceRate = $totalScheduledMinutes > 0
            ? (int) round(($totalAttendedMinutes / $totalScheduledMinutes) * 100)
            : null;

        $weekStart = now()->startOfWeek(Carbon::SATURDAY);
        $weekEnd = now()->endOfWeek(Carbon::FRIDAY);
        $weekChart = $this->weeklyChart($teacher->id, $courseIds, $exclusiveCourseIds, $weekStart, $weekEnd);
        $monthChart = $this->monthlyChart($teacher->id, $courseIds, $exclusiveCourseIds);
        $chart = [
            'week' => $weekChart,
            'month' => $monthChart,
        ];

        $overallProgressRows = $this->teacherLearningMatrix($teacher->id, $courseIds, $exclusiveCourseIds)
            ->groupBy('courses.id', 'courses.title')
            ->select('courses.id')
            ->selectRaw('ROUND(COALESCE(AVG(COALESCE(progress.progress_percent, 0)), 0)) as progress_average');
        $overallProgressAverage = (int) round((float) (
            DB::query()->fromSub($overallProgressRows, 'course_progress')->avg('progress_average') ?? 0
        ));

        $coursePageIds = $courses->getCollection()->pluck('id');
        $courseProgressData = $this->courseProgress($teacher->id, $coursePageIds, $exclusiveCourseIds);

        // Aggregate submissions once for every course instead of issuing one
        // additional query per course (the previous implementation was N+1).
        $submissionsByCourse = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->where('assignments.teacher_id', $teacher->id)
            ->whereIn('assignments.course_id', $coursePageIds)
            ->whereNotNull('submissions.submitted_at')
            ->groupBy('assignments.course_id')
            ->select('assignments.course_id')
            ->selectRaw('COUNT(*) as submissions_count')
            ->pluck('submissions_count', 'course_id');

        $courseReports = $courses;
        $courseReports->setCollection($courses->getCollection()->map(function ($course) use ($courseProgressData, $submissionsByCourse) {
            $progressRow = $courseProgressData->firstWhere('course_id', $course->id);
            $studentCount = $progressRow->student_count ?? 0;
            $progressAverage = $progressRow->progress_average ?? 0;
            $submissionsCount = (int) ($submissionsByCourse->get($course->id) ?? 0);

            return (object) [
                'id' => $course->id,
                'title' => $course->title,
                'level' => $course->level,
                'status' => $course->status,
                'is_published' => $course->isPublished(),
                'student_count' => $studentCount,
                'progress_average' => $progressAverage,
                'sections_count' => (int) $course->sections_count,
                'lessons_count' => (int) $course->lessons_count,
                'assignments_count' => (int) $course->assignments_count,
                'submissions_count' => $submissionsCount,
                'exams_count' => (int) $course->exams_count,
                'live_classes_count' => (int) $course->live_classes_count,
            ];
        }));

        $classroomAttendanceMap = $classroomPageIds !== []
            ? DB::table('attendances')
                ->whereIn('classroom_id', $classroomPageIds)
                ->whereNotNull('scheduled_minutes')
                ->whereNotNull('attended_minutes')
                ->where('scheduled_minutes', '>', 0)
                ->whereIn('status', ['present', 'late', 'absent'])
                ->groupBy('classroom_id')
                ->select('classroom_id')
                ->selectRaw('SUM(scheduled_minutes) as scheduled')
                ->selectRaw('SUM(attended_minutes) as attended')
                ->selectRaw('COUNT(*) as sessions_count')
                ->get()
                ->keyBy('classroom_id')
            : collect();

        $classroomStudentCounts = $classroomPageIds !== []
            ? DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomPageIds)
                ->where('status', 'active')
                ->groupBy('classroom_id')
                ->select('classroom_id', DB::raw('COUNT(student_id) as student_count'))
                ->pluck('student_count', 'classroom_id')
            : collect();

        $classroomReports = $classrooms;
        $classroomReports->setCollection($classrooms->getCollection()->map(function ($classroom) use ($classroomAttendanceMap, $classroomStudentCounts) {
            $att = $classroomAttendanceMap->get($classroom->id);
            $scheduled = (int) ($att->scheduled ?? 0);
            $attended = min($scheduled, (int) ($att->attended ?? 0));
            $rate = $scheduled > 0 ? (int) round(($attended / $scheduled) * 100) : null;

            return (object) [
                'id' => $classroom->id,
                'title' => $classroom->title,
                'course_title' => $classroom->course?->title ?? 'بدون دوره',
                'student_count' => (int) ($classroomStudentCounts->get($classroom->id) ?? 0),
                'scheduled_minutes' => $scheduled,
                'attended_minutes' => $attended,
                'attendance_rate' => $rate,
                'measured_sessions' => (int) ($att->sessions_count ?? 0),
            ];
        }));

        $studentProgressQuery = fn () => $this->teacherLearningMatrix($teacher->id, $courseIds, $exclusiveCourseIds)
            ->groupBy('learners.id', 'learners.name')
            ->select(['learners.id', 'learners.name'])
            ->selectRaw('ROUND(COALESCE(AVG(progress.progress_percent), 0)) as progress_average')
            ->selectRaw('COUNT(DISTINCT enrollments.course_id) as courses_count');

        $topStudents = $studentProgressQuery()
            ->orderByDesc('progress_average')
            ->orderBy('learners.id')
            ->limit(5)
            ->get()
            ->map(function ($student): object {
                $student->progress_average = (int) $student->progress_average;
                return $student;
            });

        $studentsNeedingFollowup = $studentProgressQuery()
            ->havingRaw('COALESCE(AVG(progress.progress_percent), 0) < ?', [25])
            ->orderBy('progress_average')
            ->orderBy('learners.id')
            ->limit(5)
            ->get()
            ->map(function ($student): object {
                $student->progress_average = (int) $student->progress_average;
                return $student;
            });

        $allStudentIds = $topStudents->pluck('id')->merge($studentsNeedingFollowup->pluck('id'))->unique();
        $avatarUsers = User::query()
            ->whereIn('id', $allStudentIds->all())
            ->with(['studentProfile.media' => fn ($query) => $query
                ->wherePivot('collection', 'student-avatar')
                ->orderByPivot('sort_order')])
            ->get(['id'])
            ->keyBy('id');

        $topStudents->each(function ($row) use ($avatarUsers): void {
            $avatar = $avatarUsers->get((int) $row->id)?->studentProfile?->media?->first();
            $row->avatar_url = $avatar && $avatar->visibility === 'public' ? $avatar->url() : null;
        });

        $studentsNeedingFollowup->each(function ($row) use ($avatarUsers): void {
            $avatar = $avatarUsers->get((int) $row->id)?->studentProfile?->media?->first();
            $row->avatar_url = $avatar && $avatar->visibility === 'public' ? $avatar->url() : null;
        });

        return [
            'teacher' => $teacher,
            'reportDate' => PersianUi::date(now()),
            'metrics' => [
                'studentCount' => $studentCount,
                'courseCount' => $courseCount,
                'classroomCount' => $classroomCount,
                'overallProgressAverage' => $overallProgressAverage,
                'overallAttendanceRate' => $overallAttendanceRate,
                'completedLiveClassesCount' => $completedLiveClassesCount,
                'totalLiveClassesCount' => $totalLiveClassesCount,
                'pendingAssignmentReviews' => $pendingAssignmentReviews,
                'pendingExamReviews' => $pendingExamReviews,
                'pendingReviews' => $pendingAssignmentReviews + $pendingExamReviews,
                'gradedAssignmentSubmissions' => $gradedAssignmentSubmissions,
                'gradedExamAttempts' => $gradedExamAttempts,
                'examAverage' => $examAverage !== null ? round((float) $examAverage, 1) : null,
                'totalAssignmentsCount' => (int) DB::table('assignments')->where('teacher_id', $teacher->id)->whereIn('course_id', $courseIds)->count(),
                'totalExamsCount' => (int) DB::table('exams')->where('teacher_id', $teacher->id)->whereIn('course_id', $courseIds)->count(),
            ],
            'chart' => $chart,
            'courseReports' => $courseReports,
            'classroomReports' => $classroomReports,
            'topStudents' => $topStudents,
            'studentsNeedingFollowup' => $studentsNeedingFollowup,
        ];
    }

    private function exclusiveCourseIdsForTeacher(int $teacherId, $courseIds)
    {
        return DB::table('course_teacher')
            ->select('course_id')
            ->whereIn('course_id', $courseIds)
            ->groupBy('course_id')
            ->havingRaw('COUNT(DISTINCT teacher_id) = 1')
            ->havingRaw('MAX(teacher_id) = ?', [$teacherId]);
    }

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

                $scope->orWhere(function ($directEnrollment) use ($exclusiveCourseIds): void {
                    $directEnrollment->whereNull('enrollments.classroom_id')
                        ->whereIn('courses.id', $exclusiveCourseIds);
                });
            });
    }

    private function courseProgress(int $teacherId, $courseIds, $exclusiveCourseIds)
    {
        return $this->teacherLearningMatrix($teacherId, $courseIds, $exclusiveCourseIds)
            ->whereIn('courses.id', $courseIds)
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
            $labels[] = $this->faDigits('بازه ' . ($index + 1));
            $values[] = $periods[$index] === []
                ? 0
                : (int) round(array_sum($periods[$index]) / count($periods[$index]));
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function emptyState(User $teacher, int $classroomCount, int $studentCount): array
    {
        return [
            'teacher' => $teacher,
            'reportDate' => PersianUi::date(now()),
            'metrics' => [
                'studentCount' => $studentCount,
                'courseCount' => 0,
                'classroomCount' => $classroomCount,
                'overallProgressAverage' => 0,
                'overallAttendanceRate' => null,
                'completedLiveClassesCount' => 0,
                'totalLiveClassesCount' => 0,
                'pendingAssignmentReviews' => 0,
                'pendingExamReviews' => 0,
                'pendingReviews' => 0,
                'gradedAssignmentSubmissions' => 0,
                'gradedExamAttempts' => 0,
                'examAverage' => null,
                'totalAssignmentsCount' => 0,
                'totalExamsCount' => 0,
            ],
            'chart' => [
                'week' => ['labels' => [], 'values' => []],
                'month' => ['labels' => [], 'values' => []],
            ],
            'courseReports' => collect(),
            'classroomReports' => collect(),
            'topStudents' => collect(),
            'studentsNeedingFollowup' => collect(),
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

