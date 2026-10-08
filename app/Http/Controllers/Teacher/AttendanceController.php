<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AttendanceRequest;
use App\Models\Classroom;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.attendance.index', [
            'classrooms' => $workspace->classroomsPaginated(request()->user()),
        ]);
    }

    public function edit(Classroom $classroom, TeacherWorkspaceService $workspace): View
    {
        $classroom = $workspace->classroomWithStudents(request()->user(), $classroom->id);
        $attendanceDate = request()->date('attendance_date')?->toDateString() ?? today()->toDateString();
        $existingAttendance = $classroom->attendance()
            ->whereDate('attendance_date', $attendanceDate)
            ->pluck('status', 'student_id');

        return view('teacher.attendance.form', compact(
            'classroom',
            'attendanceDate',
            'existingAttendance',
        ));
    }

    public function store(
        AttendanceRequest $request,
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->markAttendance(
            $request->user(),
            $classroom,
            $request->validated('attendance'),
            $request->validated('attendance_date'),
        );

        return back()->with('success', 'حضور و غیاب ثبت شد.');
    }
}
