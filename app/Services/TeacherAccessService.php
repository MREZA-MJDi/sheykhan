<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class TeacherAccessService
{
    public function canTeachCourse(User $teacher, Course|int $course): bool
    {
        if (! $teacher->hasRole('teacher')) {
            return false;
        }

        $courseId = $course instanceof Course ? $course->getKey() : $course;

        return DB::table('course_teacher as ct')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'courses.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('academy_user as au', function ($join) use ($teacher): void {
                $join->on('au.academy_id', '=', 'courses.academy_id')
                    ->where('au.user_id', '=', $teacher->id)
                    ->where('au.role', '=', 'teacher')
                    ->where('au.status', '=', 'active');
            })
            ->where('ct.course_id', $courseId)
            ->where('ct.teacher_id', $teacher->id)
            ->exists();
    }

    public function canManageAssignment(User $teacher, Assignment $assignment): bool
    {
        if (
            (int) $assignment->teacher_id !== (int) $teacher->id
            || ! $this->canTeachCourse($teacher, (int) $assignment->course_id)
        ) {
            return false;
        }

        return $assignment->classroom_id === null
            || $this->teacherOwnsCourseClassroom($teacher, (int) $assignment->classroom_id, (int) $assignment->course_id);
    }

    public function canManageExam(User $teacher, Exam $exam): bool
    {
        if (
            (int) $exam->teacher_id !== (int) $teacher->id
            || ! $this->canTeachCourse($teacher, (int) $exam->course_id)
        ) {
            return false;
        }

        return $exam->classroom_id === null
            || $this->teacherOwnsCourseClassroom($teacher, (int) $exam->classroom_id, (int) $exam->course_id);
    }

    public function canManageClassroom(User $teacher, Classroom $classroom): bool
    {
        if (! $teacher->hasRole('teacher')) {
            return false;
        }

        return DB::table('classroom_teacher as ct')
            ->join('classrooms', 'classrooms.id', '=', 'ct.classroom_id')
            ->join('academies', function ($join): void {
                $join->on('academies.id', '=', 'classrooms.academy_id')
                    ->where('academies.status', '=', 'active');
            })
            ->join('academy_user as au', function ($join) use ($teacher): void {
                $join->on('au.academy_id', '=', 'classrooms.academy_id')
                    ->where('au.user_id', '=', $teacher->id)
                    ->where('au.role', '=', 'teacher')
                    ->where('au.status', '=', 'active');
            })
            ->where('ct.classroom_id', $classroom->id)
            ->where('ct.teacher_id', $teacher->id)
            ->where('classrooms.status', '=', 'active')
            ->exists();
    }

    public function canManageLiveClass(User $teacher, LiveClass $liveClass): bool
    {
        if (
            (int) $liveClass->teacher_id !== (int) $teacher->id
            || ! $this->canTeachCourse($teacher, (int) $liveClass->course_id)
        ) {
            return false;
        }

        return $liveClass->classroom_id === null
            || $this->teacherOwnsCourseClassroom($teacher, (int) $liveClass->classroom_id, (int) $liveClass->course_id);
    }

    private function teacherOwnsCourseClassroom(User $teacher, int $classroomId, int $courseId): bool
    {
        return Classroom::query()
            ->whereKey($classroomId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->whereHas('academy', fn ($academy) => $academy
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->exists();
    }
}
