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
            ->with(['course:id,title,slug', 'classroom:id,title,code', 'teacher:id,name'])
            ->orderBy('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        $classes->getCollection()->transform(function (LiveClass $class) use ($student, $access): LiveClass {
            $class->can_join = $access->canJoinLiveClass($student, $class);
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
            $schedule->can_join = $access->canJoinClassSchedule($student, $schedule);
        });

        return view('student.live-classes.index', [
            'classes' => $classes,
            'weeklySchedules' => $weeklySchedules,
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
