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
        $query = DB::query()
            ->fromSub($this->studentProgressQueryByAcademies($academyIds), 'student_progress')
            ->join('course_teacher as ct', 'ct.course_id', '=', 'student_progress.course_id')
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
