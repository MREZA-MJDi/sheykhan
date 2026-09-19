<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\AttendanceRequest;
use App\Models\Classroom;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function edit(Classroom $classroom, TeacherWorkspaceService $workspace): View
    {
        $classroom = $workspace->classroomOwnedBy(request()->user(), $classroom->id);
        $classroom->load(['course:id,title', 'students:id,name']);

        return view('teacher.attendance.form', compact('classroom'));
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
