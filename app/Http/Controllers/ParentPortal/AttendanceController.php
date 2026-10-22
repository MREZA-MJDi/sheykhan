<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $childIds = $request->user()->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->select('users.id');

        $attendance = Attendance::query()
            ->whereIn('student_id', $childIds)
            ->with(['student:id,name', 'classroom:id,title,course_id', 'classroom.course:id,title'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('parent.attendance.index', compact('attendance'));
    }
}
