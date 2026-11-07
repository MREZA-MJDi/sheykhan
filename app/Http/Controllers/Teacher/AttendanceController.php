<?php

namespace App\\Http\\Controllers\\Teacher;

use App\\Http\\Controllers\\Controller;
use App\\Http\\Requests\\Teacher\\AttendanceRequest;
use App\\Models\\Attendance;
use App\\Models\\Classroom;
use App\\Services\\TeacherWorkspaceService;
use App\\Support\\PersianUi;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Carbon;
use Illuminate\\View\\View;

class AttendanceController extends Controller
{
    public function index(Request $request, TeacherWorkspaceService $workspace): View
    {
        $validated = $request->validate([
            'classroom_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'in:present,late,absent,excused'],
        ]);

        $teacher = $request->user();
        $classrooms = $workspace->classroomsPaginated($teacher, 100);
        $selectedClassroomId = isset($validated['classroom_id']) ? (int) $validated['classroom_id'] : null;

        if ($selectedClassroomId) {
            $workspace->classroomOwnedBy($teacher, $selectedClassroomId);
        }

        $ownedClassroomIds = $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($academy) => $academy
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->select('classrooms.id');

        $historyQuery = Attendance::query()
            ->with(['student:id,name', 'classroom:id,title'])
            ->whereIn('classroom_id', $ownedClassroomIds)
            ->when($selectedClassroomId, fn ($query) => $query->where('classroom_id', $selectedClassroomId))
            ->when($validated['from'] ?? null, fn ($query, $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when($validated['to'] ?? null, fn ($query, $date) => $query->whereDate('attendance_date', '<=', $date))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status));

        $historySummary = (clone $historyQuery)
            ->reorder()
            ->selectRaw('COUNT(*) as records_count')
            ->selectRaw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count")
            ->selectRaw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count")
            ->selectRaw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count")
            ->selectRaw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count")
            ->first();

        $attendanceHistory = $historyQuery
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('teacher.attendance.index', [
            'classrooms' => $classrooms,
            'attendanceHistory' => $attendanceHistory,
            'historySummary' => $historySummary,
            'filters' => $validated,
        ]);
    }

    public function edit(Classroom $classroom, TeacherWorkspaceService $workspace): View
    {
        $classroom = $workspace->classroomWithStudents(request()->user(), $classroom->id);

        $attendanceDate = PersianUi::normalizeDate(
            is_string(request()->input('attendance_date'))
                ? request()->input('attendance_date')
                : null
        ) ?? today()->toDateString();

        $existingAttendance = $classroom->attendance()
            ->whereDate('attendance_date', $attendanceDate)
            ->get(['student_id', 'status', 'scheduled_minutes', 'attended_minutes'])
            ->keyBy('student_id');
        $scheduledMinutes = $workspace->scheduledMinutesForDate($classroom, $attendanceDate);

        return view('teacher.attendance.form', compact(
            'classroom',
            'attendanceDate',
            'existingAttendance',
            'scheduledMinutes',
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
            $request->validated('attendance_minutes', []),
        );

        return back()->with('success', 'حضور و غیاب ثبت شد.');
    }
}
