<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        if (! $activeMembership || ! $course->teachers()->whereKey($teacher->id)->exists()) {
            throw new AccessDeniedHttpException('این دوره برای مدرس شما قابل مشاهده نیست.');
        }

        $lessons = $course->sections()
            ->with(['lessons' => fn ($query) => $query
                ->select('id', 'course_section_id', 'title', 'sort_order')
                ->where('status', 'published')
                ->where(fn ($published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->orderBy('sort_order')])
            ->get()
            ->flatMap(fn ($section) => $section->lessons)
            ->values();

        // A course can have multiple teachers, each responsible for a different
        // classroom. Never treat course_id alone as permission to see a roster.
        $teacherClassroomIds = $teacher->classroomsAsTeacher()
            ->where('classrooms.course_id', $course->id)
            ->where('classrooms.status', 'active')
            ->whereHas('academy', fn ($academy) => $academy
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->pluck('classrooms.id');

        /** @var LengthAwarePaginator $students */
        $students = User::query()
            ->whereRaw('1 = 0')
            ->when($teacherClassroomIds->isNotEmpty(), fn ($query) => $query
                ->whereHas('enrollments', fn ($enrollment) => $enrollment
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->whereIn('classroom_id', $teacherClassroomIds))
                ->whereHas('classroomsAsStudent', fn ($classroom) => $classroom
                    ->whereIn('classrooms.id', $teacherClassroomIds)
                    ->wherePivot('status', 'active')))
            ->orderBy('users.id')
            ->paginate(20)
            ->withQueryString();

        $studentCollection = $students->getCollection();

        $progressRows = $lessons->isEmpty() || $studentCollection->isEmpty()
            ? collect()
            : DB::table('lesson_progress')
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->whereIn('user_id', $studentCollection->pluck('id'))
                ->get([
                    'lesson_id',
                    'user_id',
                    'progress_percent',
                    'seconds_watched',
                    'completed_at',
                    'last_watched_at',
                ]);

        $progress = $progressRows->mapWithKeys(fn ($row) => [
            $row->user_id . ':' . $row->lesson_id => $row,
        ]);

        $studentSummary = $studentCollection->map(function (User $student) use ($lessons, $progress) {
            $values = $lessons->map(fn ($lesson) =>
                (float) ($progress[$student->id . ':' . $lesson->id]->progress_percent ?? 0));

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
