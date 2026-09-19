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

        $courseIds = $teacher->taughtCourses()->pluck('courses.id');

        return User::query()
            ->whereHas('classroomsAsStudent', fn ($query) => $query
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active'))
            ->with('studentProfile')
            ->withCount([
                'lessonProgress as progress_items_count' => fn ($query) => $query
                    ->whereHas('lesson.section', fn ($section) => $section
                        ->whereIn('course_id', $courseIds)),
                'assignmentSubmissions as assignment_submissions_count' => fn ($query) => $query
                    ->whereHas('assignment', fn ($assignment) => $assignment
                        ->where('teacher_id', $teacher->id)),
                'examAttempts as exam_attempts_count' => fn ($query) => $query
                    ->whereHas('exam', fn ($exam) => $exam
                        ->where('teacher_id', $teacher->id)),
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

    public function classroomOwnedByCourse(User $teacher, int $classroomId, int $courseId): Classroom
    {
        return $teacher->classroomsAsTeacher()
            ->whereKey($classroomId)
            ->where('classrooms.course_id', $courseId)
            ->firstOrFail();
    }

    public function classroomDetails(User $teacher, Classroom $classroom): Classroom
    {
        $classroom = $this->classroomOwnedBy($teacher, $classroom->id);

        return $classroom->load([
            'course:id,title,slug,status',
            'students:id,name,email',
            'students.studentProfile',
            'schedules' => fn ($query) => $query->orderBy('weekday')->orderBy('start_time'),
            'teachers:id,name',
        ]);
    }

    public function studentDetails(User $teacher, User $student): array
    {
        $classroomIds = $teacher->classroomsAsTeacher()->pluck('classrooms.id');

        abort_if(
            $classroomIds->isEmpty()
            || !$student->classroomsAsStudent()
                ->whereIn('classrooms.id', $classroomIds)
                ->where('classroom_student.status', 'active')
                ->exists(),
            404
        );

        $courseIds = $teacher->taughtCourses()->pluck('courses.id');

        $enrollments = $student->enrollments()
            ->whereIn('course_id', $courseIds)
            ->where('status', 'active')
            ->with(['course:id,title,academy_id'])
            ->latest('id')
            ->get();

        $lessonProgress = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('progress.user_id', $student->id)
            ->whereIn('course_sections.course_id', $courseIds)
            ->orderByDesc('progress.last_watched_at')
            ->limit(40)
            ->get([
                'progress.lesson_id',
                'progress.progress_percent',
                'progress.seconds_watched',
                'progress.completed_at',
                'progress.last_watched_at',
                'lessons.title as lesson_title',
                'course_sections.course_id',
                'course_sections.title as section_title',
            ]);

        $classrooms = $student->classroomsAsStudent()
            ->whereIn('classrooms.id', $classroomIds)
            ->where('classroom_student.status', 'active')
            ->with('course:id,title')
            ->get();

        return [
            'student' => $student->load('studentProfile'),
            'classrooms' => $classrooms,
            'enrollments' => $enrollments,
            'progress' => $lessonProgress,
            'assignmentCount' => $student->assignmentSubmissions()
                ->whereHas('assignment', fn ($query) => $query->whereIn('course_id', $courseIds))
                ->count(),
            'examAttemptCount' => $student->examAttempts()
                ->whereHas('exam', fn ($query) => $query->whereIn('course_id', $courseIds))
                ->count(),
        ];
    }

    public function markAttendance(User $teacher, Classroom $classroom, array $attendance, string $attendanceDate): void
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
                        'attendance_date' => $attendanceDate,
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
