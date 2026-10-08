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
use Illuminate\Support\Facades\DB;

final class TeacherWorkspaceService
{
    public function courses(User $teacher): Collection
    {
        return $teacher->taughtCourses()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with('academy:id,name')
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                'sections',
            ])
            ->latest('courses.updated_at')
            ->get();
    }

    public function coursesPaginated(User $teacher, int $perPage = 12): LengthAwarePaginator
    {
        return $teacher->taughtCourses()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with('academy:id,name')
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                'sections',
                'classrooms',
            ])
            ->latest('courses.updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function classrooms(User $teacher): Collection
    {
        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with('course:id,title')
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
                'schedules',
            ])
            ->orderBy('title')
            ->get();
    }

    public function classroomsPaginated(User $teacher, int $perPage = 12): LengthAwarePaginator
    {
        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with('course:id,title')
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
                'schedules',
            ])
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function classroomsForSchedule(User $teacher): Collection
    {
        return $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with([
                'course:id,title',
                'schedules:id,classroom_id,weekday,start_time,end_time,room,meeting_url',
            ])
            ->orderBy('title')
            ->get();
    }

    public function classroomWithStudents(User $teacher, int $classroomId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
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
        $classroomIds = $teacher->classroomsAsTeacher()->pluck('classrooms.id');

        if ($classroomIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active'))
            ->with('studentProfile')
            ->withCount([
                'lessonProgress as progress_items_count',
                'assignmentSubmissions',
                'examAttempts',
            ])
            ->orderBy('name')
            ->get();
    }

    public function studentsPaginated(
        User $teacher,
        int $perPage = 20,
        string $sort = 'name',
        string $direction = 'asc'
    ): LengthAwarePaginator {
        $classroomIds = $teacher->classroomsAsTeacher()->pluck('classrooms.id');

        if ($classroomIds->isEmpty()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $allowedSorts = [
            'name' => 'name',
            'progress' => 'progress_items_count',
            'assignments' => 'assignment_submissions_count',
            'exams' => 'exam_attempts_count',
        ];

        $sortColumn = $allowedSorts[$sort] ?? $allowedSorts['name'];
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return User::query()
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active'))
            ->with('studentProfile')
            ->withCount([
                'lessonProgress as progress_items_count',
                'assignmentSubmissions',
                'examAttempts',
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
            ->with(['course:id,title', 'classroom:id,title'])
            ->withCount([
                'questions',
                'attempts as submitted_attempts_count' => fn ($query) => $query->where('status', 'submitted'),
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
            ->with(['course:id,title', 'classroom:id,title'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function courseOwnedBy(User $teacher, int $courseId): Course
    {
        return $teacher->taughtCourses()
            ->whereKey($courseId)
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();
    }

    public function classroomOwnedBy(User $teacher, int $classroomId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();
    }

    public function classroomOwnedByCourse(User $teacher, int $classroomId, int $courseId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->where('course_id', $courseId)
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();
    }

    public function scheduleOwnedBy(User $teacher, int $scheduleId): ClassSchedule
    {
        return ClassSchedule::query()
            ->whereKey($scheduleId)
            ->whereHas('classroom', fn ($query) => $query
                ->whereHas('teachers', fn ($teacherQuery) => $teacherQuery->whereKey($teacher->id))
                ->whereHas('academy', fn ($academy) => $academy->where('status', 'active')))
            ->firstOrFail();
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

    public function markAttendance(User $teacher, Classroom $classroom, array $attendance, string $attendanceDate): void
    {
        // Keep ownership and student membership checks separate and batched.
        // This intentionally avoids loading the full Classroom model graph.
        $this->classroomOwnedBy($teacher, $classroom->id);

        $studentIds = $classroom->students()
            ->wherePivot('status', 'active')
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        DB::transaction(function () use ($teacher, $classroom, $attendance, $attendanceDate, $studentIds): void {
            $allowedStudentIds = array_fill_keys($studentIds, true);
            $rows = [];

            foreach ($attendance as $studentId => $status) {
                if (!isset($allowedStudentIds[(int) $studentId])) {
                    continue;
                }

                $rows[] = [
                    'classroom_id' => $classroom->id,
                    'student_id' => (int) $studentId,
                    'attendance_date' => $attendanceDate,
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
                    ['marked_by', 'status', 'note', 'updated_at'],
                );
            }
        });
    }
}
