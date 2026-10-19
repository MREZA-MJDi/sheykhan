<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Attendance;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Rule;
use Illuminate\View\View;

final class AttendanceController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')->where('academy_id', $academy->id)],
            'attendance_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['present', 'late', 'absent', 'excused'])],
        ]);

        $query = Attendance::query()
            ->whereHas('classroom', fn ($classrooms) => $classrooms->where('academy_id', $academy->id))
            ->with([
                'student:id,name,email',
                'classroom:id,academy_id,course_id,title',
                'classroom.course:id,title',
                'marker:id,name',
            ]);

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->whereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%')
                ->orWhere('email', 'like', '%' . $term . '%'));
        }

        if (filled($filters['classroom_id'] ?? null)) {
            $query->where('classroom_id', (int) $filters['classroom_id']);
        }

        if (filled($filters['attendance_date'] ?? null)) {
            $query->whereDate('attendance_date', $filters['attendance_date']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        $summary = (clone $query)->reorder()->selectRaw(
            "COUNT(*) AS total,
             SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
             SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
             SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
             SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) AS excused_count"
        )->first();

        $attendances = $query
            ->orderByDesc('attendance_date')
            ->orderBy('classroom_id')
            ->orderBy('student_id')
            ->paginate(25)
            ->withQueryString();

        $classrooms = $academy->classrooms()
            ->orderBy('title')
            ->get(['id', 'title', 'course_id']);

        return view('owner.attendance.index', compact('academy', 'filters', 'summary', 'attendances', 'classrooms'));
    }
}
