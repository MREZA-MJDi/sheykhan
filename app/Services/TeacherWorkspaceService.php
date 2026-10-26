<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class TeacherWorkspaceService
{
    public function courses(User $teacher): Collection
    {
        $courses = $teacher->taughtCourses()
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with('academy:id,name')
            ->withCount(['sections'])
            ->latest('courses.updated_at')
            ->get();

        $this->attachTeacherVisibleStudentCounts($courses, $teacher);

        return $courses;
    }

    public function coursesPaginated(User $teacher, int $perPage = 12): LengthAwarePaginator
    {
        $courses = $teacher->taughtCourses()
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with('academy:id,name')
            ->withCount(['sections', 'classrooms'])
            ->latest('courses.updated_at')
            ->paginate($perPage)
            ->withQueryString();

        $this->attachTeacherVisibleStudentCounts($courses, $teacher);

        return $courses;
    }

    /**
     * Counts only students in this teacher's active classroom cohorts. A course
     * may be shared publicly and assigned to several teachers, so counting every
     * active course enrollment here would inflate the number and expose a
     * misleading roster metric.
     */
    private function attachTeacherVisibleStudentCounts($courses, User $teacher): void
    {
        $items = $courses instanceof LengthAwarePaginator
            ? $courses->getCollection()
            : $courses;

        $courseIds = $items->pluck('id');
        if ($courseIds->isEmpty()) {
            return;
        }

        $counts = DB::table('classroom_teacher as assigned_teachers')
            ->join('classrooms', 'classrooms.id', '=', 'assigned_teachers.classroom_id')
            ->join('classroom_student as memberships', function ($join): void {
                $join->on('memberships.classroom_id', '=', 'classrooms.id')
                    ->where('memberships.status', '=', 'active');
            })
            ->join('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.classroom_id', '=', 'classrooms.id')
                    ->on('enrollments.student_id', '=', 'memberships.student_id')
                    ->on('enrollments.course_id', '=', 'classrooms.course_id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->where('assigned_teachers.teacher_id', $teacher->id)
            ->where('classrooms.status', 'active')
            ->whereIn('classrooms.course_id', $courseIds)
            ->groupBy('classrooms.course_id')
            ->select('classrooms.course_id')
            ->selectRaw('COUNT(DISTINCT memberships.student_id) as visible_student_count')
            ->pluck('visible_student_count', 'course_id');

        foreach ($items as $course) {
            $course->setAttribute('active_students_count', (int) ($counts[$course->id] ?? 0));
        }
    }

    public function classrooms(User $teacher): Collection
    {
        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with('course:id,title')
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active')
                    ->where('users.status', 'active')
                    ->whereNull('users.deleted_at')
                    ->whereExists(function ($enrollment): void {
                        $enrollment->selectRaw('1')
                            ->from('course_enrollments')
                            ->whereColumn('course_enrollments.classroom_id', 'classroom_student.classroom_id')
                            ->whereColumn('course_enrollments.course_id', 'classrooms.course_id')
                            ->whereColumn('course_enrollments.student_id', 'users.id')
                            ->where('course_enrollments.status', 'active');
                    }),
                'schedules',
            ])
            ->orderBy('title')
            ->get();
    }

    public function classroomsPaginated(User $teacher, int $perPage = 12): LengthAwarePaginator
    {
        $search = trim((string) request()->query('q', ''));
        $status = in_array(request()->query('status'), ['active', 'archived'], true)
            ? (string) request()->query('status')
            : '';

        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with('course:id,title')
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active')
                    ->where('users.status', 'active')
                    ->whereNull('users.deleted_at')
                    ->whereExists(function ($enrollment): void {
                        $enrollment->selectRaw('1')
                            ->from('course_enrollments')
                            ->whereColumn('course_enrollments.classroom_id', 'classroom_student.classroom_id')
                            ->whereColumn('course_enrollments.course_id', 'classrooms.course_id')
                            ->whereColumn('course_enrollments.student_id', 'users.id')
                            ->where('course_enrollments.status', 'active');
                    }),
                'schedules',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($matches) use ($search): void {
                    $matches->where('classrooms.title', 'like', '%' . $search . '%')
                        ->orWhere('classrooms.code', 'like', '%' . $search . '%')
                        ->orWhereHas('course', fn ($course) => $course->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('classrooms.status', $status))
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function classroomsForSchedule(User $teacher): Collection
    {
        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with([
                'course:id,title',
                'schedules:id,classroom_id,weekday,start_time,end_time,room,provider,meeting_url',
            ])
            ->orderBy('title')
            ->get();
    }

    public function classroomWithStudents(User $teacher, int $classroomId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with([
                'course:id,title',
                'students' => fn ($query) => $query
                    ->select('users.id', 'users.name')
                    ->wherePivot('status', 'active')
                    ->orderBy('users.name'),
            ])
            ->firstOrFail();
    }

    public function students(User $teacher): Collection
    {
        $classroomIds = $teacher->classroomsAsTeacher()
            ->where('classrooms.status', 'active')
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->pluck('classrooms.id');

        if ($classroomIds->isEmpty()) {
            return collect();
        }

        $courseIds = DB::table('classrooms')->whereIn('id', $classroomIds)->distinct()->pluck('course_id');

        return User::query()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active')
                ->whereExists(function ($enrollment): void {
                    $enrollment->selectRaw('1')
                        ->from('course_enrollments')
                        ->whereColumn('course_enrollments.classroom_id', 'classrooms.id')
                        ->whereColumn('course_enrollments.course_id', 'classrooms.course_id')
                        ->whereColumn('course_enrollments.student_id', 'users.id')
                        ->where('course_enrollments.status', 'active');
                }))
            ->with('studentProfile')
            ->withCount([
                'lessonProgress as progress_items_count' => fn ($query) => $query
                    ->whereHas('lesson', fn ($lesson) => $lesson
                        ->where('status', 'published')
                        ->whereHas('section', fn ($section) => $section->whereIn('course_id', $courseIds))),
                'assignmentSubmissions' => fn ($query) => $query->whereHas('assignment', fn ($assignment) => $assignment
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('course_id', $courseIds)),
                'examAttempts' => fn ($query) => $query->whereHas('exam', fn ($exam) => $exam
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('course_id', $courseIds)),
            ])
            ->orderBy('name')
            ->get();
    }

    public function studentsPaginated(
        User $teacher,
        int $perPage = 20,
        string $sort = 'name',
        string $direction = 'asc',
        string $search = ''
    ): LengthAwarePaginator {
        // Keep the roster small, searchable and limited to active classes visible
        // to this teacher; never fetch the complete academy roster into memory.
        $search = trim(mb_substr($search, 0, 100));

        // Use the same active-academy membership boundary as the other teacher
        // workspace queries. Archived memberships must not leak into the roster.
        $classroomIds = $teacher->classroomsAsTeacher()
            ->where('classrooms.status', 'active')
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->pluck('classrooms.id');

        if ($classroomIds->isEmpty()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $courseIds = DB::table('classrooms')->whereIn('id', $classroomIds)->distinct()->pluck('course_id');

        $allowedSorts = [
            'name' => 'name',
            'progress' => 'progress_items_count',
            'assignments' => 'assignment_submissions_count',
            'exams' => 'exam_attempts_count',
        ];

        $sortColumn = $allowedSorts[$sort] ?? $allowedSorts['name'];
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return User::query()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active')
                ->whereExists(function ($enrollment): void {
                    $enrollment->selectRaw('1')
                        ->from('course_enrollments')
                        ->whereColumn('course_enrollments.classroom_id', 'classrooms.id')
                        ->whereColumn('course_enrollments.course_id', 'classrooms.course_id')
                        ->whereColumn('course_enrollments.student_id', 'users.id')
                        ->where('course_enrollments.status', 'active');
                }))
            ->with([
                'studentProfile',
                'studentProfile.media' => fn ($query) => $query
                    ->wherePivot('collection', 'student-avatar')
                    ->orderByPivot('sort_order'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $needle = '%' . addcslashes($search, '\\%_') . '%';
                $query->where(function ($studentQuery) use ($needle): void {
                    $studentQuery
                        ->where('users.name', 'like', $needle)
                        ->orWhere('users.email', 'like', $needle)
                        ->orWhereHas('studentProfile', fn ($profileQuery) => $profileQuery
                            ->where('student_number', 'like', $needle));
                });
            })
            ->withCount([
                'lessonProgress as progress_items_count' => fn ($query) => $query
                    ->whereHas('lesson', fn ($lesson) => $lesson
                        ->where('status', 'published')
                        ->whereHas('section', fn ($section) => $section->whereIn('course_id', $courseIds))),
                'assignmentSubmissions' => fn ($query) => $query->whereHas('assignment', fn ($assignment) => $assignment
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('course_id', $courseIds)),
                'examAttempts' => fn ($query) => $query->whereHas('exam', fn ($exam) => $exam
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('course_id', $courseIds)),
            ])
            ->orderBy($sortColumn, $direction)
            ->orderBy('users.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function assignments(User $teacher, int $perPage = 15): LengthAwarePaginator
    {
        return Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->whereHas('course.academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with(['course:id,title', 'classroom:id,title'])
            ->withCount([
                'submissions as submitted_count' => fn ($query) => $query->whereNotNull('submitted_at'),
                'submissions as pending_review_count' => fn ($query) => $query
                    ->whereNotNull('submitted_at')
                    ->whereNull('graded_at'),
            ])
            ->latest('due_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function exams(User $teacher, int $perPage = 15): LengthAwarePaginator
    {
        return Exam::query()
            ->where('teacher_id', $teacher->id)
            ->whereHas('course.academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with(['course:id,title', 'classroom:id,title'])
            ->withCount([
                'questions',
                'attempts as attempts_count',
                // Attempts move straight to pending_review or graded when submitted;
                // count submission timestamps rather than a transient status name.
                'attempts as submitted_attempts_count' => fn ($query) => $query->whereNotNull('submitted_at'),
                'attempts as graded_attempts_count' => fn ($query) => $query->where('status', 'graded'),
            ])
            ->latest('starts_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function liveClasses(User $teacher, int $perPage = 15): LengthAwarePaginator
    {
        return LiveClass::query()
            ->where('teacher_id', $teacher->id)
            ->whereHas('course.academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->with(['course:id,title', 'classroom:id,title', 'recording:id,original_name,mime_type,size,status,visibility'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function courseOwnedBy(User $teacher, int $courseId): Course
    {
        return $teacher->taughtCourses()
            ->whereKey($courseId)
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->firstOrFail();
    }

    public function classroomOwnedBy(User $teacher, int $classroomId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->firstOrFail();
    }

    public function classroomOwnedByCourse(User $teacher, int $classroomId, int $courseId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->where('course_id', $courseId)
            ->whereHas('academy', fn ($query) => $this->activeTeacherAcademy($query, $teacher->id))
            ->firstOrFail();
    }

    public function scheduleOwnedBy(User $teacher, int $scheduleId): ClassSchedule
    {
        return ClassSchedule::query()
            ->whereKey($scheduleId)
            ->whereHas('classroom', fn ($query) => $query
                ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->whereKey($teacher->id))
                ->whereHas('academy', fn ($academy) => $this->activeTeacherAcademy($academy, $teacher->id)))
            ->firstOrFail();
    }


    private function activeTeacherAcademy($query, int $teacherId)
    {
        return $query
            ->where('status', 'active')
            ->whereHas('users', fn ($membership) => $membership
                ->whereKey($teacherId)
                ->where('academy_user.role', 'teacher')
                ->where('academy_user.status', 'active'));
    }

    public function storeSchedule(
        User $teacher,
        Classroom $classroom,
        array $data
    ): ClassSchedule {
        $this->classroomOwnedBy($teacher, $classroom->id);

        $overlaps = $classroom->schedules()
            ->where('weekday', $data['weekday'])
            ->where(function ($query) use ($data): void {
                $query
                    ->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($overlaps) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_time' => 'این بازه زمانی با یکی از زمان‌های ثبت‌شده کلاس هم‌پوشانی دارد.',
            ]);
        }

        return $classroom->schedules()->create($data);
    }

    public function deleteSchedule(User $teacher, int $scheduleId): void
    {
        $schedule = $this->scheduleOwnedBy($teacher, $scheduleId);
        $schedule->delete();
    }

    public function scheduledMinutesForDate(Classroom $classroom, string $attendanceDate): int
    {
        $weekday = Carbon::parse($attendanceDate)->dayOfWeek;

        return (int) $classroom->schedules()
            ->where('weekday', $weekday)
            ->get(['start_time', 'end_time'])
            ->sum(function ($schedule): int {
                [$startHour, $startMinute] = array_map('intval', explode(':', substr((string) $schedule->start_time, 0, 5)));
                [$endHour, $endMinute] = array_map('intval', explode(':', substr((string) $schedule->end_time, 0, 5)));

                return max(0, ($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute));
            });
    }

    public function markAttendance(
        User $teacher,
        Classroom $classroom,
        array $attendance,
        string $attendanceDate,
        array $attendanceMinutes = [],
    ): void
    {
        // Keep ownership and student membership checks separate and batched.
        // This intentionally avoids loading the full Classroom model graph.
        $this->classroomOwnedBy($teacher, $classroom->id);

        $studentIds = $classroom->students()
            ->wherePivot('status', 'active')
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $allowed = array_fill_keys($studentIds, true);
        $submittedIds = array_map('intval', array_keys($attendance));

        if (array_diff($submittedIds, $studentIds) !== [] || array_diff($studentIds, $submittedIds) !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attendance' => 'فهرست دانش‌آموزان تغییر کرده است. صفحه را تازه کن و حضور همه دانش‌آموزان فعال را ثبت کن.',
            ]);
        }

        $scheduledMinutes = $this->scheduledMinutesForDate($classroom, $attendanceDate);

        foreach ($attendance as $studentId => $status) {
            $id = (int) $studentId;
            if (! isset($allowed[$id])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'attendance' => 'یک دانش‌آموز انتخاب‌شده عضو فعال این کلاس نیست.',
                ]);
            }

            $rawMinutes = $attendanceMinutes[$studentId] ?? $attendanceMinutes[$id] ?? null;
            $actualMinutes = $rawMinutes === '' || $rawMinutes === null ? null : (int) $rawMinutes;

            if ($scheduledMinutes > 0 && $status === 'late' && $actualMinutes === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "attendance_minutes.{$studentId}" => 'برای دانش‌آموزی که با تأخیر آمده، دقیقه‌های حضور واقعی را وارد کن.',
                ]);
            }

            if ($scheduledMinutes > 0 && $actualMinutes !== null && $actualMinutes > $scheduledMinutes) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "attendance_minutes.{$studentId}" => 'مدت حضور نمی‌تواند از مدت برنامه‌ریزی‌شده کلاس بیشتر باشد.',
                ]);
            }
        }

        DB::transaction(function () use ($teacher, $classroom, $attendance, $attendanceDate, $attendanceMinutes, $scheduledMinutes): void {
            $rows = [];

            foreach ($attendance as $studentId => $status) {
                $rawMinutes = $attendanceMinutes[$studentId] ?? $attendanceMinutes[(int) $studentId] ?? null;
                $actualMinutes = $rawMinutes === '' || $rawMinutes === null ? null : (int) $rawMinutes;

                if ($scheduledMinutes < 1) {
                    $scheduledForRow = null;
                    $attendedForRow = null;
                } else {
                    $scheduledForRow = $scheduledMinutes;
                    $attendedForRow = match ($status) {
                        'absent' => 0,
                        'excused' => null,
                        'present' => min($scheduledMinutes, $actualMinutes ?? $scheduledMinutes),
                        'late' => $actualMinutes,
                        default => null,
                    };
                }

                $rows[] = [
                    'classroom_id' => $classroom->id,
                    'student_id' => (int) $studentId,
                    'attendance_date' => $attendanceDate,
                    'scheduled_minutes' => $scheduledForRow,
                    'attended_minutes' => $attendedForRow,
                    'marked_by' => $teacher->id,
                    'status' => $status,
                    'note' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows) {
                DB::table('attendances')->upsert(
                    $rows,
                    ['classroom_id', 'student_id', 'attendance_date'],
                    ['marked_by', 'status', 'scheduled_minutes', 'attended_minutes', 'note', 'updated_at'],
                );
            }
        });
    }
}
