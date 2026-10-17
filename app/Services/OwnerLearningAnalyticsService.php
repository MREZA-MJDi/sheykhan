<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class OwnerLearningAnalyticsService
{
    public function courseProgress($courseIds)
    {
        return $this->studentProgressQuery($courseIds)
            ->get()
            ->groupBy('course_id')
            ->map(fn ($rows) => round((float) $rows->avg('progress_percent'), 1));
    }

    public function teacherProgress($courseIds, $teacherIds = null)
    {
        $query = DB::query()
            ->fromSub($this->studentProgressQuery($courseIds), 'student_progress')
            ->join('course_teacher as ct', 'ct.course_id', '=', 'student_progress.course_id')
            ->whereIn('ct.course_id', $courseIds)
            ->groupBy('ct.teacher_id')
            ->select([
                'ct.teacher_id',
                DB::raw('ROUND(AVG(student_progress.progress_percent), 1) AS progress_average'),
            ]);

        if ($teacherIds !== null) {
            $query->whereIn('ct.teacher_id', $teacherIds);
        }

        return $query
            ->pluck('progress_average', 'ct.teacher_id')
            ->map(fn ($value) => (float) $value);
    }

    public function teacherProgressForAcademies($academyIds, $teacherIds = null)
    {
        // Teacher analytics must follow the actual classroom cohort, not every
        // active enrollment attached to a course shared by several teachers.
        $teacherStudentProgress = DB::query()
            ->fromSub($this->studentProgressQueryByAcademies($academyIds), 'student_progress')
            ->join('classrooms as teacher_classrooms', function ($join) use ($academyIds): void {
                $join->on('teacher_classrooms.course_id', '=', 'student_progress.course_id')
                    ->whereIn('teacher_classrooms.academy_id', $academyIds)
                    ->where('teacher_classrooms.status', '=', 'active');
            })
            ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'teacher_classrooms.id')
            ->join('classroom_student as roster', function ($join): void {
                $join->on('roster.classroom_id', '=', 'teacher_classrooms.id')
                    ->on('roster.student_id', '=', 'student_progress.student_id')
                    ->where('roster.status', '=', 'active');
            })
            ->join('course_enrollments as matching_enrollment', function ($join): void {
                $join->on('matching_enrollment.classroom_id', '=', 'teacher_classrooms.id')
                    ->on('matching_enrollment.course_id', '=', 'student_progress.course_id')
                    ->on('matching_enrollment.student_id', '=', 'student_progress.student_id')
                    ->where('matching_enrollment.status', '=', 'active');
            })
            ->groupBy('teaching_assignment.teacher_id', 'student_progress.course_id', 'student_progress.student_id')
            ->select([
                'teaching_assignment.teacher_id',
                'student_progress.course_id',
                'student_progress.student_id',
            ])
            ->selectRaw('AVG(student_progress.progress_percent) AS progress_average');

        $query = DB::query()
            ->fromSub($teacherStudentProgress, 'teacher_student_progress')
            ->groupBy('teacher_id')
            ->select([
                'teacher_id',
                DB::raw('ROUND(AVG(progress_average), 1) AS progress_average'),
            ]);

        if ($teacherIds !== null) {
            $query->whereIn('teacher_id', $teacherIds);
        }

        return $query
            ->pluck('progress_average', 'teacher_id')
            ->map(fn ($value) => (float) $value);
    }

    /**
     * One row per teacher, counting only students actively enrolled in a
     * specific active classroom assigned to that teacher.
     */
    public function teacherRosterCountsQuery($academyIds)
    {
        return DB::table('classroom_teacher as teaching_assignment')
            ->join('classrooms as classrooms', function ($join) use ($academyIds): void {
                $join->on('classrooms.id', '=', 'teaching_assignment.classroom_id')
                    ->whereIn('classrooms.academy_id', $academyIds)
                    ->where('classrooms.status', '=', 'active');
            })
            ->join('classroom_student as roster', function ($join): void {
                $join->on('roster.classroom_id', '=', 'classrooms.id')
                    ->where('roster.status', '=', 'active');
            })
            ->join('course_enrollments as enrollment', function ($join): void {
                $join->on('enrollment.classroom_id', '=', 'classrooms.id')
                    ->on('enrollment.course_id', '=', 'classrooms.course_id')
                    ->on('enrollment.student_id', '=', 'roster.student_id')
                    ->where('enrollment.status', '=', 'active');
            })
            ->whereIn('classrooms.academy_id', $academyIds)
            ->groupBy('teaching_assignment.teacher_id')
            ->select('teaching_assignment.teacher_id')
            ->selectRaw('COUNT(DISTINCT enrollment.student_id) AS student_count');
    }

    public function courseExamAverages($courseIds)
    {
        return $this->latestExamAttemptsQuery($courseIds)
            ->get()
            ->groupBy('course_id')
            ->map(fn ($rows) => round((float) $rows->avg('score'), 1));
    }

    public function overallExamAverage($courseIds): ?float
    {
        if (empty($courseIds)) {
            return null;
        }

        $average = DB::query()
            ->fromSub($this->latestExamAttemptsQuery($courseIds), 'latest_attempts')
            ->avg('score');

        return $average === null ? null : round((float) $average, 1);
    }

    public function overallExamAverageForAcademies($academyIds): ?float
    {
        if (empty($academyIds)) {
            return null;
        }

        $average = DB::query()
            ->fromSub($this->latestExamAttemptsByAcademiesQuery($academyIds), 'latest_attempts')
            ->avg('score');

        return $average === null ? null : round((float) $average, 1);
    }

    private function studentProgressQueryByAcademies($academyIds)
    {
        return DB::table('course_enrollments as enrollments')
            ->join('courses as courses', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('course_sections as sections', 'sections.course_id', '=', 'enrollments.course_id')
            ->leftJoin('lessons as lessons', function ($join): void {
                $join->on('lessons.course_section_id', '=', 'sections.id')
                    ->where('lessons.status', '=', 'published')
                    ->whereNotNull('lessons.published_at')
                    ->where('lessons.published_at', '<=', now());
            })
            ->leftJoin('lesson_progress as progress', function ($join): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->on('progress.user_id', '=', 'enrollments.student_id');
            })
            ->whereIn('courses.academy_id', $academyIds)
            ->where('enrollments.status', 'active')
            ->groupBy('enrollments.course_id', 'enrollments.student_id')
            ->selectRaw(
                'enrollments.course_id, enrollments.student_id,
                 COUNT(lessons.id) AS lesson_count,
                 COALESCE(SUM(progress.progress_percent), 0) AS progress_sum'
            )
            ->selectRaw(
                'CASE
                    WHEN COUNT(lessons.id) = 0 THEN 0
                    WHEN COALESCE(SUM(progress.progress_percent), 0) / COUNT(lessons.id) > 100 THEN 100
                    ELSE COALESCE(SUM(progress.progress_percent), 0) / COUNT(lessons.id)
                 END AS progress_percent'
            );
    }

    private function studentProgressQuery($courseIds)
    {
        return DB::table('course_enrollments as enrollments')
            ->leftJoin('course_sections as sections', 'sections.course_id', '=', 'enrollments.course_id')
            ->leftJoin('lessons as lessons', function ($join): void {
                $join->on('lessons.course_section_id', '=', 'sections.id')
                    ->where('lessons.status', '=', 'published')
                    ->whereNotNull('lessons.published_at')
                    ->where('lessons.published_at', '<=', now());
            })
            ->leftJoin('lesson_progress as progress', function ($join): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->on('progress.user_id', '=', 'enrollments.student_id');
            })
            ->whereIn('enrollments.course_id', $courseIds)
            ->where('enrollments.status', 'active')
            ->groupBy('enrollments.course_id', 'enrollments.student_id')
            ->selectRaw(
                'enrollments.course_id, enrollments.student_id,
                 COUNT(lessons.id) AS lesson_count,
                 COALESCE(SUM(progress.progress_percent), 0) AS progress_sum'
            )
            ->selectRaw(
                'CASE
                    WHEN COUNT(lessons.id) = 0 THEN 0
                    WHEN COALESCE(SUM(progress.progress_percent), 0) / COUNT(lessons.id) > 100 THEN 100
                    ELSE COALESCE(SUM(progress.progress_percent), 0) / COUNT(lessons.id)
                 END AS progress_percent'
            );
    }

    private function latestExamAttemptsByAcademiesQuery($academyIds)
    {
        return DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->whereIn('courses.academy_id', $academyIds)
            ->whereNotNull('attempts.submitted_at')
            ->whereNotNull('attempts.score')
            ->whereRaw(
                'attempts.attempt_number = (
                    SELECT MAX(previous.attempt_number)
                    FROM exam_attempts AS previous
                    WHERE previous.exam_id = attempts.exam_id
                      AND previous.student_id = attempts.student_id
                      AND previous.submitted_at IS NOT NULL
                      AND previous.score IS NOT NULL
                )'
            )
            ->select([
                'exams.course_id',
                'attempts.exam_id',
                'attempts.student_id',
                'attempts.score',
            ]);
    }

    private function latestExamAttemptsQuery($courseIds)
    {
        return DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->whereIn('exams.course_id', $courseIds)
            ->whereNotNull('attempts.submitted_at')
            ->whereNotNull('attempts.score')
            ->whereRaw(
                'attempts.attempt_number = (
                    SELECT MAX(previous.attempt_number)
                    FROM exam_attempts AS previous
                    WHERE previous.exam_id = attempts.exam_id
                      AND previous.student_id = attempts.student_id
                      AND previous.submitted_at IS NOT NULL
                      AND previous.score IS NOT NULL
                )'
            )
            ->select([
                'exams.course_id',
                'attempts.exam_id',
                'attempts.student_id',
                'attempts.score',
            ]);
    }
}
