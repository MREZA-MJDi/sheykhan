<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\StudentMessage;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class StudentMessageController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user();

        $messages = StudentMessage::query()
            ->where('student_id', $student->id)
            ->with([
                'teacher:id,name',
                'course:id,title',
                'classroom:id,title',
                'responder:id,name',
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('student.messages.index', [
            'messages' => $messages,
            'contactableTargets' => $this->contactableTargets($student),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^(course|classroom):\d+:\d+$/'],
            'category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'مدرس و دوره یا کلاس مرتبط را انتخاب کن.',
            'target.regex' => 'انتخاب مدرس یا کلاس معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کن.',
            'body.required' => 'متن پیام را وارد کن.',
        ]);

        [$type, $contextId, $teacherId] = explode(':', $data['target'], 3);
        $student = $request->user();

        $context = $this->resolveTarget($student, $type, (int) $contextId, (int) $teacherId);

        StudentMessage::query()->create([
            'academy_id' => $context['academy_id'],
            'student_id' => $student->id,
            'teacher_id' => (int) $teacherId,
            'course_id' => $context['course_id'],
            'classroom_id' => $context['classroom_id'],
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'status' => 'open',
        ]);

        return redirect()->route('student.messages.index')
            ->with('success', 'پیامت برای مدرس مرتبط ارسال شد. پاسخ در همین صفحه نمایش داده می‌شود.');
    }

    /**
     * Available recipients come only from active course enrollments or active
     * classroom memberships and active teacher memberships in the same academy.
     * The identifiers are re-validated during store; a crafted form cannot
     * create a message to an unrelated teacher.
     */
    private function contactableTargets(User $student)
    {
        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student)->all();
        if ($courseIds === []) {
            return collect();
        }

        $activeClassroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->where('classrooms.status', 'active')
            ->whereIn('classrooms.course_id', $courseIds)
            ->pluck('classrooms.id');

        $courseTargets = DB::table('course_enrollments as enrollment')
            ->join('courses', 'courses.id', '=', 'enrollment.course_id')
            ->join('course_teacher as assignment', 'assignment.course_id', '=', 'courses.id')
            ->join('users as teacher', 'teacher.id', '=', 'assignment.teacher_id')
            ->join('academy_user as membership', function ($join): void {
                $join->on('membership.user_id', '=', 'teacher.id')
                    ->on('membership.academy_id', '=', 'courses.academy_id')
                    ->where('membership.role', '=', 'teacher')
                    ->where('membership.status', '=', 'active');
            })
            ->where('enrollment.student_id', $student->id)
            ->whereIn('enrollment.course_id', $courseIds)
            ->where(function ($cohort): void {
                $cohort->whereNull('enrollment.classroom_id')
                    ->orWhereExists(function ($eligibleClassroom): void {
                        $eligibleClassroom->selectRaw('1')
                            ->from('classrooms as cohort_classroom')
                            ->join('classroom_teacher as cohort_assignment', 'cohort_assignment.classroom_id', '=', 'cohort_classroom.id')
                            ->whereColumn('cohort_classroom.id', 'enrollment.classroom_id')
                            ->whereColumn('cohort_classroom.course_id', 'enrollment.course_id')
                            ->whereColumn('cohort_assignment.teacher_id', 'assignment.teacher_id')
                            ->where('cohort_classroom.status', 'active');
                    });
            })
            ->where('teacher.status', 'active')
            ->selectRaw("'course' as target_type, courses.id as course_id, NULL as classroom_id, courses.academy_id, teacher.id as teacher_id, teacher.name as teacher_name, courses.title as context_title")
            ->distinct()
            ->get();

        $classroomTargets = DB::table('classroom_student as student_membership')
            ->join('classrooms', 'classrooms.id', '=', 'student_membership.classroom_id')
            ->join('classroom_teacher as assignment', 'assignment.classroom_id', '=', 'classrooms.id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('users as teacher', 'teacher.id', '=', 'assignment.teacher_id')
            ->join('academy_user as membership', function ($join): void {
                $join->on('membership.user_id', '=', 'teacher.id')
                    ->on('membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('membership.role', '=', 'teacher')
                    ->where('membership.status', '=', 'active');
            })
            ->where('student_membership.student_id', $student->id)
            ->whereIn('classrooms.id', $activeClassroomIds)
            ->where('student_membership.status', 'active')
            ->where('classrooms.status', 'active')
            ->where('teacher.status', 'active')
            ->selectRaw("'classroom' as target_type, classrooms.course_id as course_id, classrooms.id as classroom_id, classrooms.academy_id, teacher.id as teacher_id, teacher.name as teacher_name, classrooms.title as context_title")
            ->distinct()
            ->get();

        return $courseTargets->merge($classroomTargets)
            ->unique(fn ($target): string => $target->target_type . ':' . ($target->classroom_id ?: $target->course_id) . ':' . $target->teacher_id)
            ->values()
            ->map(function ($target): object {
                $contextId = $target->classroom_id ?: $target->course_id;
                $target->key = $target->target_type . ':' . $contextId . ':' . $target->teacher_id;
                return $target;
            });
    }

    /**
     * Resolve and authorize the submitted context again on the server.
     *
     * @return array{academy_id:int,course_id:int|null,classroom_id:int|null}
     */
    private function resolveTarget(User $student, string $type, int $contextId, int $teacherId): array
    {
        if ($type === 'course') {
            $course = Course::query()
                ->whereKey($contextId)
                ->firstOrFail();

            abort_unless(app(StudentAccessService::class)->course($student, $course), 404);

            $assigned = DB::table('course_teacher as assignment')
                ->join('users as teacher', 'teacher.id', '=', 'assignment.teacher_id')
                ->join('academy_user as membership', function ($join) use ($course): void {
                    $join->on('membership.user_id', '=', 'teacher.id')
                        ->where('membership.academy_id', '=', $course->academy_id)
                        ->where('membership.role', '=', 'teacher')
                        ->where('membership.status', '=', 'active');
                })
                ->where('assignment.course_id', $course->id)
                ->where('assignment.teacher_id', $teacherId)
                ->where('teacher.status', 'active')
                ->whereExists(function ($enrollments) use ($student, $course, $teacherId): void {
                    $enrollments->selectRaw('1')
                        ->from('course_enrollments as enrollment')
                        ->where('enrollment.student_id', $student->id)
                        ->where('enrollment.course_id', $course->id)
                        ->where('enrollment.status', 'active')
                        ->where(function ($cohort) use ($teacherId): void {
                            $cohort->whereNull('enrollment.classroom_id')
                                ->orWhereExists(function ($eligibleClassroom) use ($teacherId): void {
                                    $eligibleClassroom->selectRaw('1')
                                        ->from('classrooms as cohort_classroom')
                                        ->join('classroom_teacher as cohort_assignment', 'cohort_assignment.classroom_id', '=', 'cohort_classroom.id')
                                        ->whereColumn('cohort_classroom.id', 'enrollment.classroom_id')
                                        ->whereColumn('cohort_classroom.course_id', 'enrollment.course_id')
                                        ->whereColumn('cohort_assignment.teacher_id', 'assignment.teacher_id')
                                        ->where('cohort_classroom.status', 'active')
                                        ->where('cohort_assignment.teacher_id', $teacherId);
                                });
                        });
                })
                ->exists();

            abort_unless($assigned, 404);

            return [
                'academy_id' => (int) $course->academy_id,
                'course_id' => (int) $course->id,
                'classroom_id' => null,
            ];
        }

        abort_unless($type === 'classroom', 404);

        $classroom = Classroom::query()
            ->with('course')
            ->whereKey($contextId)
            ->where('status', 'active')
            ->firstOrFail();

        abort_unless($classroom->course && app(StudentAccessService::class)->course($student, $classroom->course), 404);

        $isStudent = DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        $assigned = DB::table('classroom_teacher as assignment')
            ->join('users as teacher', 'teacher.id', '=', 'assignment.teacher_id')
            ->join('academy_user as membership', function ($join) use ($classroom): void {
                $join->on('membership.user_id', '=', 'teacher.id')
                    ->where('membership.academy_id', '=', $classroom->academy_id)
                    ->where('membership.role', '=', 'teacher')
                    ->where('membership.status', '=', 'active');
            })
            ->where('assignment.classroom_id', $classroom->id)
            ->where('assignment.teacher_id', $teacherId)
            ->where('teacher.status', 'active')
            ->exists();

        abort_unless($isStudent && $assigned, 404);

        return [
            'academy_id' => (int) $classroom->academy_id,
            'course_id' => $classroom->course_id ? (int) $classroom->course_id : null,
            'classroom_id' => (int) $classroom->id,
        ];
    }
}
