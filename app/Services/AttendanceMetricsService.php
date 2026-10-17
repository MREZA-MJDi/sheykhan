<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AttendanceMetricsService
{
    /**
     * The percentage is the share of scheduled classroom minutes actually
     * attended. Excused absences are excluded from the denominator. Historical
     * rows without minute data are reported as unmeasured, never guessed.
     */
    public function forStudent(User $student): array
    {
        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->select('classrooms.id');

        $totals = DB::table('attendances')
            ->where('student_id', $student->id)
            ->whereIn('classroom_id', $classroomIds)
            ->whereNotNull('scheduled_minutes')
            ->whereNotNull('attended_minutes')
            ->where('scheduled_minutes', '>', 0)
            ->whereIn('status', ['present', 'late', 'absent'])
            ->selectRaw('COALESCE(SUM(scheduled_minutes), 0) AS scheduled_minutes')
            ->selectRaw('COALESCE(SUM(attended_minutes), 0) AS attended_minutes')
            ->selectRaw('COUNT(*) AS measured_sessions')
            ->first();

        $scheduled = (int) ($totals->scheduled_minutes ?? 0);
        $attended = min($scheduled, (int) ($totals->attended_minutes ?? 0));

        $unmeasuredSessions = DB::table('attendances')
            ->where('student_id', $student->id)
            ->whereIn('classroom_id', $classroomIds)
            ->where(function ($query): void {
                $query->whereNull('scheduled_minutes')
                    ->orWhereNull('attended_minutes')
                    ->orWhere('scheduled_minutes', '<=', 0);
            })
            ->whereIn('status', ['present', 'late', 'absent'])
            ->count();

        return [
            'scheduled_minutes' => $scheduled,
            'attended_minutes' => $attended,
            'attendance_percent' => $scheduled > 0
                ? (int) round($attended / $scheduled * 100)
                : null,
            'measured_sessions' => (int) ($totals->measured_sessions ?? 0),
            'unmeasured_sessions' => (int) $unmeasuredSessions,
        ];
    }

    /**
     * Return measured attendance ratios for only the students in the current
     * teacher's active classroom rosters.
     *
     * @return array<int, array{scheduled_minutes:int,attended_minutes:int,attendance_percent:?int,measured_sessions:int,unmeasured_sessions:int}>
     */
    public function forTeacher(User $teacher): array
    {
        $classroomIds = $teacher->classroomsAsTeacher()
            ->whereHas('academy', fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('users', fn ($membership) => $membership
                    ->whereKey($teacher->id)
                    ->where('academy_user.role', 'teacher')
                    ->where('academy_user.status', 'active')))
            ->select('classrooms.id');

        $rows = DB::table('attendances')
            ->join('classroom_student as membership', function ($join): void {
                $join->on('membership.classroom_id', '=', 'attendances.classroom_id')
                    ->on('membership.student_id', '=', 'attendances.student_id')
                    ->where('membership.status', '=', 'active');
            })
            ->whereIn('attendances.classroom_id', $classroomIds)
            ->whereNotNull('attendances.scheduled_minutes')
            ->whereNotNull('attendances.attended_minutes')
            ->where('attendances.scheduled_minutes', '>', 0)
            ->whereIn('attendances.status', ['present', 'late', 'absent'])
            ->groupBy('attendances.student_id')
            ->select('attendances.student_id')
            ->selectRaw('SUM(attendances.scheduled_minutes) AS scheduled_minutes')
            ->selectRaw('SUM(attendances.attended_minutes) AS attended_minutes')
            ->selectRaw('COUNT(*) AS measured_sessions')
            ->get();

        $metrics = [];
        foreach ($rows as $row) {
            $scheduled = (int) $row->scheduled_minutes;
            $attended = min($scheduled, (int) $row->attended_minutes);
            $metrics[(int) $row->student_id] = [
                'scheduled_minutes' => $scheduled,
                'attended_minutes' => $attended,
                'attendance_percent' => $scheduled > 0 ? (int) round($attended / $scheduled * 100) : null,
                'measured_sessions' => (int) $row->measured_sessions,
                'unmeasured_sessions' => 0,
            ];
        }

        return $metrics;
    }
}
