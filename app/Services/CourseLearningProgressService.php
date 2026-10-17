<?php

namespace App\\Services;

use App\\Models\\Course;
use App\\Models\\User;
use Illuminate\\Contracts\\Pagination\\LengthAwarePaginator;
use Illuminate\\Support\\Facades\\DB;
use Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException;

final class CourseLearningProgressService
{
    public function __construct(private readonly TeacherAccessService $teacherAccess) {}

    public function forTeacher(User $teacher, Course $course): array
    {
        if (! $this->teacherAccess->canTeachCourse($teacher, $course)) {
            throw new AccessDeniedHttpException('این دوره به حساب مدرس شما اختصاص داده نشده است.');
        }

        // A public course can be taught by several teachers. Roster visibility
        // comes from the exact active classroom cohort, never course_id alone.
        $classroomIds = $teacher->classroomsAsTeacher()
            ->where('classrooms.course_id', $course->id)
            ->where('classrooms.status', 'active')
            ->whereHas('academy', fn ($academy) => $academy
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->pluck('classrooms.id');

        $lessons = $course->sections()
            ->with(['lessons' => fn ($query) => $query
                ->select('id', 'course_section_id', 'title', 'sort_order')
                ->where('status', 'published')
                ->where(fn ($published) => $published
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now()))
                ->orderBy('sort_order')])
            ->get()
            ->flatMap(fn ($section) => $section->lessons)
            ->values();

        $studentQuery = User::query()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
            ->whereIn('id', function ($query) use ($teacher, $course, $classroomIds): void {
                $query->select('enrollments.student_id')
                    ->from('course_enrollments as enrollments')
                    ->join('classroom_student as roster', function ($join): void {
                        $join->on('roster.student_id', '=', 'enrollments.student_id')
                            ->on('roster.classroom_id', '=', 'enrollments.classroom_id')
                            ->where('roster.status', '=', 'active');
                    })
                    ->join('classroom_teacher as assigned', function ($join) use ($teacher): void {
                        $join->on('assigned.classroom_id', '=', 'enrollments.classroom_id')
                            ->where('assigned.teacher_id', '=', $teacher->id);
                    })
                    ->join('classrooms', function ($join) use ($course): void {
                        $join->on('classrooms.id', '=', 'enrollments.classroom_id')
                            ->where('classrooms.course_id', '=', $course->id)
                            ->where('classrooms.status', '=', 'active');
                    })
                    ->where('enrollments.course_id', $course->id)
                    ->where('enrollments.status', 'active')
                    ->whereIn('enrollments.classroom_id', $classroomIds)
                    ->distinct();

                if (! $course->isFree()) {
                    $query->where('enrollments.payment_status', 'paid')
                        ->where('enrollments.paid_amount', '>', 0)
                        ->where(function ($amount): void {
                            $amount->whereNull('enrollments.price_amount')
                                ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                        })
                        ->whereExists(function ($transaction): void {
                            $transaction->selectRaw('1')
                                ->from('financial_transactions')
                                ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                ->where('financial_transactions.status', 'completed')
                                ->where('financial_transactions.amount', '>', 0);
                        });
                }
            })
            ->orderBy('users.id');

        /** @var LengthAwarePaginator $students */
        $students = $classroomIds->isEmpty()
            ? (clone $studentQuery)->whereRaw('1 = 0')->paginate(20)->withQueryString()
            : $studentQuery->paginate(20)->withQueryString();

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
                (float) ($progress[$student->id . ':' . $lesson->id]->progress_percent ?? 0)
            );

            return [
                'student' => $student,
                'progress' => $values->isEmpty() ? null : (int) round((float) $values->avg()),
                'completed_lessons' => $values->filter(fn ($value) => $value >= 100)->count(),
                'total_lessons' => $lessons->count(),
                'progress_method' => 'میانگین درصد ثبت‌شده برای درس‌های منتشرشده همین دوره؛ این عدد نمره آزمون یا نرخ حضور نیست.',
            ];
        });

        return [
            'course' => $course->loadMissing('academy:id,name'),
            'lessons' => $lessons,
            'students' => $students,
            'progress' => $progress,
            'studentSummary' => $studentSummary,
            'rosterClassroomCount' => $classroomIds->count(),
        ];
    }
}
