<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CourseLearningProgressService
{
    public function forTeacher(User $teacher, Course $course): array
    {
        $activeMembership = DB::table('academy_user')
            ->where('academy_id', $course->academy_id)
            ->where('user_id', $teacher->id)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->exists();

        if (!$activeMembership || !$course->teachers()->whereKey($teacher->id)->exists()) {
            throw new AccessDeniedHttpException('این دوره برای مدرس شما قابل مشاهده نیست.');
        }

        $lessons = $course->sections()
            ->with(['lessons' => fn ($query) => $query
                ->select('id', 'course_section_id', 'title', 'sort_order')
                ->orderBy('sort_order')])
            ->get()
            ->flatMap(fn ($section) => $section->lessons)
            ->values();

        $students = $course->enrollments()
            ->where('status', 'active')
            ->with(['student:id,name'])
            ->orderBy('id')
            ->get()
            ->map(fn ($enrollment) => $enrollment->student)
            ->filter()
            ->unique('id')
            ->values();

        $progressRows = DB::table('lesson_progress')
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->whereIn('user_id', $students->pluck('id'))
            ->get([
                'lesson_id',
                'user_id',
                'progress_percent',
                'seconds_watched',
                'completed_at',
                'last_watched_at',
            ]);

        $progress = $progressRows->mapWithKeys(function ($row) {
            return [
                $row->user_id . ':' . $row->lesson_id => $row,
            ];
        });

        $studentSummary = $students->map(function (User $student) use ($lessons, $progress) {
            $values = $lessons->map(function ($lesson) use ($student, $progress) {
                return (float) ($progress[$student->id . ':' . $lesson->id]->progress_percent ?? 0);
            });

            return [
                'student' => $student,
                'progress' => round((float) ($values->avg() ?? 0)),
                'completed_lessons' => $values->filter(fn ($value) => $value >= 100)->count(),
            ];
        });

        return [
            'course' => $course->loadMissing('academy:id,name'),
            'lessons' => $lessons,
            'students' => $students,
            'progress' => $progress,
            'studentSummary' => $studentSummary,
        ];
    }
}
