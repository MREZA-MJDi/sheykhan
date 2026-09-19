<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TeacherWorkspaceService
{
    public function courses(User $teacher): Collection
    {
        return $teacher->taughtCourses()
            ->with('academy:id,name')
            ->withCount([
                'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                'sections',
            ])
            ->latest('courses.updated_at')
            ->get();
    }

    public function classrooms(User $teacher): Collection
    {
        return $teacher->classroomsAsTeacher()
            ->with([
                'course:id,title',
                'students:id,name',
                'schedules',
            ])
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
            ])
            ->orderBy('title')
            ->get();
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

    public function assignments(User $teacher): Collection
    {
        return Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->with('classroom:id,title')
            ->withCount([
                'submissions as submitted_count' => fn ($query) => $query->whereNotNull('submitted_at'),
                'submissions as pending_review_count' => fn ($query) => $query
                    ->whereNotNull('submitted_at')
                    ->whereNull('graded_at'),
            ])
            ->latest('due_at')
            ->get();
    }

    public function exams(User $teacher): Collection
    {
        return Exam::query()
            ->where('teacher_id', $teacher->id)
            ->with('classroom:id,title')
            ->withCount([
                'attempts as submitted_attempts_count' => fn ($query) => $query->where('status', 'submitted'),
                'attempts as graded_attempts_count' => fn ($query) => $query->where('status', 'graded'),
            ])
            ->latest('starts_at')
            ->get();
    }

    public function liveClasses(User $teacher): Collection
    {
        return LiveClass::query()
            ->where('teacher_id', $teacher->id)
            ->with(['course:id,title', 'classroom:id,title'])
            ->orderByDesc('scheduled_at')
            ->limit(30)
            ->get();
    }

    public function courseOwnedBy(User $teacher, int $courseId): Course
    {
        return $teacher->taughtCourses()
            ->whereKey($courseId)
            ->firstOrFail();
    }

    public function classroomOwnedBy(User $teacher, int $classroomId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->firstOrFail();
    }

    public function markAttendance(User $teacher, Classroom $classroom, array $attendance): void
    {
        $this->classroomOwnedBy($teacher, $classroom->id);

        DB::transaction(function () use ($teacher, $classroom, $attendance): void {
            foreach ($attendance as $studentId => $status) {
                $studentExists = $classroom->students()
                    ->whereKey($studentId)
                    ->wherePivot('status', 'active')
                    ->exists();

                if (!$studentExists) {
                    continue;
                }

                DB::table('attendances')->updateOrInsert(
                    [
                        'classroom_id' => $classroom->id,
                        'student_id' => $studentId,
                        'attendance_date' => request()->date('attendance_date', today()->toDateString()),
                    ],
                    [
                        'marked_by' => $teacher->id,
                        'status' => $status,
                        'note' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });
    }
}
