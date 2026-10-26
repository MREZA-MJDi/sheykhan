<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\PanelExportService;
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

    public function export(Request $request, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', 'in:csv,word']]);
        $childIds = $request->user()->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->select('users.id');

        $items = Attendance::query()
            ->whereIn('student_id', $childIds)
            ->with(['student:id,name', 'classroom:id,title,course_id', 'classroom.course:id,title'])
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();
        $statusLabels = ['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه'];
        $rows = $items->map(fn (Attendance $item): array => [
            $item->student?->name,
            $item->classroom?->title,
            $item->classroom?->course?->title,
            $item->attendance_date instanceof \\DateTimeInterface ? $item->attendance_date->format('Y-m-d') : (string) $item->attendance_date,
            $statusLabels[$item->status] ?? $item->status,
            $item->attended_minutes,
            $item->scheduled_minutes,
            $item->note,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'parent-child-attendance',
            'سوابق حضور و غیاب فرزندان',
            ['دانش‌آموز', 'کلاس', 'دوره', 'تاریخ', 'وضعیت', 'مدت حضور (دقیقه)', 'مدت برنامه (دقیقه)', 'یادداشت'],
            $rows,
            ['والد' => $request->user()->name, 'تعداد رکوردهای قابل مشاهده' => $rows->count()],
        );
    }
}
