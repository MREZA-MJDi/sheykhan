<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
use App\Models\ClassSchedule;
use App\Services\StudentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiveClassController extends Controller
{
    public function index(StudentAccessService $access): View
    {
        $student = request()->user();
        $courseIds = $access->enrolledCourseIds($student);

        $classes = LiveClass::query()
            ->whereIn('course_id', $courseIds)
            ->whereBetween('scheduled_at', [now()->subDays(30), now()->addDays(60)])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($student): void {
                $query->whereNull('classroom_id')
                    ->orWhereExists(function ($membership) use ($student): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student')
                            ->whereColumn('classroom_student.classroom_id', 'live_classes.classroom_id')
                            ->where('classroom_student.student_id', $student->id)
                            ->where('classroom_student.status', 'active');
                    });
            })
            // Match the protected join policy in SQL before returning any session metadata.
            // Course enrollment alone is insufficient when the same course has multiple teacher cohorts.
            ->whereExists(function ($eligible) use ($student): void {
                $eligible->selectRaw('1')
                    ->from('classroom_student as eligible_membership')
                    ->join('classrooms as eligible_classroom', 'eligible_classroom.id', '=', 'eligible_membership.classroom_id')
                    ->join('classroom_teacher as eligible_teacher', function ($join): void {
                        $join->on('eligible_teacher.classroom_id', '=', 'eligible_classroom.id')
                            ->on('eligible_teacher.teacher_id', '=', 'live_classes.teacher_id');
                    })
                    ->join('course_enrollments as eligible_enrollment', function ($join) use ($student): void {
                        $join->on('eligible_enrollment.classroom_id', '=', 'eligible_classroom.id')
                            ->on('eligible_enrollment.course_id', '=', 'eligible_classroom.course_id')
                            ->where('eligible_enrollment.student_id', '=', $student->id)
                            ->where('eligible_enrollment.status', '=', 'active');
                    })
                    ->join('courses as eligible_course', 'eligible_course.id', '=', 'eligible_enrollment.course_id')
                    ->whereColumn('eligible_classroom.course_id', 'live_classes.course_id')
                    ->where('eligible_classroom.status', 'active')
                    ->where('eligible_membership.student_id', $student->id)
                    ->where('eligible_membership.status', 'active')
                    ->where(function ($classroom): void {
                        $classroom->whereNull('live_classes.classroom_id')
                            ->orWhereColumn('live_classes.classroom_id', 'eligible_classroom.id');
                    })
                    ->where(function ($entitlement): void {
                        $entitlement->where('eligible_course.access_type', 'free')
                            ->orWhere(function ($paid): void {
                                $paid->where('eligible_enrollment.payment_status', 'paid')
                                    ->where('eligible_enrollment.paid_amount', '>', 0)
                                    ->where(function ($amount): void {
                                        $amount->whereNull('eligible_enrollment.price_amount')
                                            ->orWhereColumn('eligible_enrollment.paid_amount', '>=', 'eligible_enrollment.price_amount');
                                    })
                                    ->whereExists(function ($transaction): void {
                                        $transaction->selectRaw('1')
                                            ->from('financial_transactions')
                                            ->whereColumn('financial_transactions.enrollment_id', 'eligible_enrollment.id')
                                            ->where('financial_transactions.status', 'completed')
                                            ->where('financial_transactions.amount', '>', 0);
                                    });
                            });
                    });
            })
            ->with(['course:id,title,slug', 'classroom:id,title,code', 'teacher:id,name'])
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        $classes->getCollection()->transform(function (LiveClass $class) use ($student, $access): LiveClass {
            $class->can_join = $access->isLiveClassJoinWindowOpen($class);
            return $class;
        });

        // ClassSchedule rows repeat every week and are deliberately shown
        // separately from dated LiveClass sessions.
        $weeklySchedules = ClassSchedule::query()
            ->whereHas('classroom', fn ($query) => $query
                ->where('status', 'active')
                ->whereIn('course_id', $courseIds)
                ->whereHas('students', fn ($students) => $students
                    ->where('users.id', $student->id)
                    ->where('classroom_student.status', 'active')))
            ->with([
                'classroom:id,title,code,course_id,status',
                'classroom.course:id,title,slug,academy_id,status,access_type,published_at',
            ])
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (ClassSchedule $schedule) => $schedule->classroom?->course !== null)
            ->values();

        $weeklySchedules->each(function (ClassSchedule $schedule) use ($student, $access): void {
            $schedule->can_join = $access->isClassScheduleJoinWindowOpen($schedule);
        });

        // Keep the full recording archive separate from dated upcoming sessions.
        // This query mirrors the protected MediaPolicy cohort/entitlement checks,
        // so same-course students in another teacher group cannot discover links.
        $recordings = $courseIds->isEmpty()
            ? LiveClass::query()->whereRaw('1 = 0')->paginate(12, ['*'], 'recordings_page')
            : LiveClass::query()
                ->whereIn('course_id', $courseIds)
                ->whereIn('status', ['completed', 'published'])
                ->whereNotNull('recording_media_id')
                ->whereNotNull('recording_released_at')
                ->where('recording_released_at', '<=', now())
                ->where('scheduled_at', '<=', now())
                ->whereHas('recording', fn ($media) => $media
                    ->where('status', 'active')
                    ->where('visibility', 'private'))
                ->whereExists(function ($membership) use ($student): void {
                    $membership->selectRaw('1')
                        ->from('classroom_student as student_membership')
                        ->join('classrooms as enrolled_classroom', 'enrolled_classroom.id', '=', 'student_membership.classroom_id')
                        ->join('classroom_teacher as teaching_assignment', function ($join): void {
                            $join->on('teaching_assignment.classroom_id', '=', 'enrolled_classroom.id')
                                ->on('teaching_assignment.teacher_id', '=', 'live_classes.teacher_id');
                        })
                        ->join('course_enrollments as enrollment', function ($join) use ($student): void {
                            $join->on('enrollment.classroom_id', '=', 'enrolled_classroom.id')
                                ->on('enrollment.course_id', '=', 'enrolled_classroom.course_id')
                                ->where('enrollment.student_id', '=', $student->id)
                                ->where('enrollment.status', '=', 'active');
                        })
                        ->join('courses as enrolled_courses', 'enrolled_courses.id', '=', 'enrollment.course_id')
                        ->whereColumn('enrolled_classroom.course_id', 'live_classes.course_id')
                        ->where('enrolled_classroom.status', 'active')
                        ->where('student_membership.student_id', $student->id)
                        ->where('student_membership.status', 'active')
                        ->where(function ($classroom) {
                            $classroom->whereNull('live_classes.classroom_id')
                                ->orWhereColumn('live_classes.classroom_id', 'enrolled_classroom.id');
                        })
                        ->where(function ($entitlement): void {
                            $entitlement->where('enrolled_courses.access_type', 'free')
                                ->orWhere(function ($paid): void {
                                    $paid->where('enrollment.payment_status', 'paid')
                                        ->where('enrollment.paid_amount', '>', 0)
                                        ->where(function ($amount): void {
                                            $amount->whereNull('enrollment.price_amount')
                                                ->orWhereColumn('enrollment.paid_amount', '>=', 'enrollment.price_amount');
                                        })
                                        ->whereExists(function ($transaction): void {
                                            $transaction->selectRaw('1')
                                                ->from('financial_transactions')
                                                ->whereColumn('financial_transactions.enrollment_id', 'enrollment.id')
                                                ->where('financial_transactions.status', 'completed')
                                                ->where('financial_transactions.amount', '>', 0);
                                        });
                                });
                        });
                })
                ->with([
                    'course:id,title',
                    'classroom:id,title',
                    'teacher:id,name',
                    'recording:id,disk,path,original_name,mime_type,size,status,visibility',
                ])
                ->orderByDesc('recording_released_at')
                ->orderByDesc('id')
                ->paginate(12, ['*'], 'recordings_page')
                ->withQueryString();

        return view('student.live-classes.index', [
            'classes' => $classes,
            'weeklySchedules' => $weeklySchedules,
            'recordings' => $recordings,
        ]);
    }

    public function join(LiveClass $liveClass, StudentAccessService $access): RedirectResponse
    {
        abort_unless($access->canJoinLiveClass(request()->user(), $liveClass), 404);

        return redirect()->away($liveClass->meeting_url);
    }

    public function joinSchedule(ClassSchedule $schedule, StudentAccessService $access): RedirectResponse
    {
        abort_unless($access->canJoinClassSchedule(request()->user(), $schedule), 404);

        return redirect()->away($schedule->meeting_url);
    }
}
