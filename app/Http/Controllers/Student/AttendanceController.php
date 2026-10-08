<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentAccessService;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(StudentAccessService $access): View
    {
        $student = request()->user();
        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->pluck('classrooms.id');

        $attendance = \App\Models\Attendance::query()
            ->where('student_id', $student->id)
            ->whereIn('classroom_id', $classroomIds)
            ->with('classroom:id,title,code')
            ->orderByDesc('attendance_date')
            ->paginate(20)
            ->withQueryString();

        return view('student.attendance.index', ['attendance' => $attendance]);
    }
}
