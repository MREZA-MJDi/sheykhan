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
        if (!$teacher->hasRole('teacher')) {
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
        return (int) $assignment->teacher_id === (int) $teacher->id
            && $this->canTeachCourse($teacher, (int) $assignment->course_id);
    }

    public function canManageExam(User $teacher, Exam $exam): bool
    {
        return (int) $exam->teacher_id === (int) $teacher->id
            && $this->canTeachCourse($teacher, (int) $exam->course_id);
    }

    public function canManageClassroom(User $teacher, Classroom $classroom): bool
    {
        if (!$teacher->hasRole('teacher')) {
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
            ->exists();
    }

    public function canManageLiveClass(User $teacher, LiveClass $liveClass): bool
    {
        return (int) $liveClass->teacher_id === (int) $teacher->id
            && $this->canTeachCourse($teacher, (int) $liveClass->course_id);
    }
}
