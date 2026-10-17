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

        if (!$activeMembership || !$course->teachers()->whereKey($teacher->id)->exists()) {
            throw new AccessDeniedHttpException('این دوره برای مدرس شما قابل مشاهده نیست.');
        }

        // A course can have several teachers. In that case, course ownership alone
        // does not authorize a teacher to inspect every learner on that course.
        // Class membership is the cohort boundary for student-level reporting.
        $classroomIds = $teacher->classroomsAsTeacher()
            ->where('classrooms.course_id', $course->id)
            ->where('classrooms.status', 'active')
            ->pluck('classrooms.id');

        $classroomStudentIds = $classroomIds->isEmpty()
            ? collect()
            : DB::table('classroom_student')
                ->whereIn('classroom_id', $classroomIds)
                ->where('status', 'active')
                ->distinct()
                ->pluck('student_id');

        // A self-paced, classroom-less enrollment can be reported only when this
        // is the course's sole assigned teacher. For a co-taught course, an
        // unassigned enrollment is ambiguous and is intentionally not exposed.
        $isSoleCourseTeacher = $course->teachers()->count() === 1;
        $directStudentIds = $isSoleCourseTeacher
            ? DB::table('course_enrollments')
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->whereNull('classroom_id')
                ->pluck('student_id')
            : collect();

        $visibleStudentIds = $classroomStudentIds
            ->merge($directStudentIds)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $lessons = $course->sections()
            ->with(['lessons' => fn ($query) => $query
                ->select('id', 'course_section_id', 'title', 'sort_order', 'status', 'published_at')
                ->where('status', 'published')
                ->where(fn ($published) => $published->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->orderBy('sort_order')])
            ->get()
            ->flatMap(fn ($section) => $section->lessons)
            ->values();

        /** @var LengthAwarePaginator $students */
        $students = User::query()
            ->where('status', 'active')
            ->whereIn('users.id', $visibleStudentIds)
            ->whereHas('enrollments', function ($query) use ($course, $classroomIds, $isSoleCourseTeacher): void {
                $query
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->where(function ($cohort) use ($classroomIds, $isSoleCourseTeacher): void {
                        if ($classroomIds->isNotEmpty()) {
                            $cohort->whereIn('classroom_id', $classroomIds);
                        }

                        if ($isSoleCourseTeacher) {
                            $cohort->orWhereNull('classroom_id');
                        }
                    });
            })
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

        $progress = $progressRows->mapWithKeys(function ($row) {
            return [
                $row->user_id . ':' . $row->lesson_id => $row,
            ];
        });

        // No activity is a measured zero, not a missing student. The score is
        // explicitly a published-lesson study-progress average, not an exam grade
        // or proof of comprehension; attendance and assessment remain separate.
        $studentSummary = $studentCollection->map(function (User $student) use ($lessons, $progress) {
            $values = $lessons->map(function ($lesson) use ($student, $progress) {
                return min(100, max(0, (float) ($progress[$student->id . ':' . $lesson->id]->progress_percent ?? 0)));
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
            'progressMetricDescription' => 'میانگین درصد مطالعه درس‌های منتشرشده این دوره؛ حضور و غیاب، تکلیف و نمره آزمون جداگانه سنجیده می‌شوند.',
        ];
    }
}
