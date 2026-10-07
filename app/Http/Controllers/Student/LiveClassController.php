<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LiveClass;
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

        return view('student.live-classes.index', ['classes' => $classes]);
    }

    public function join(LiveClass $liveClass, StudentAccessService $access): RedirectResponse
    {
        abort_unless($access->canJoinLiveClass(request()->user(), $liveClass), 404);

        return redirect()->away($liveClass->meeting_url);
    }
}
