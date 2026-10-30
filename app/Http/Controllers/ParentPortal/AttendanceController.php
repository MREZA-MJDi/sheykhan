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
        $parent = $request->user();

        $childIds = $parent->children()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', function ($academies) use ($parent): void {
                $academies
                    ->where('academies.status', 'active')
                    ->where('academy_user.role', 'student')
                    ->where('academy_user.status', 'active')
                    ->whereExists(function ($membership) use ($parent): void {
                        $membership->selectRaw('1')
                            ->from('academy_user as parent_membership')
                            ->whereColumn('parent_membership.academy_id', 'academies.id')
                            ->where('parent_membership.user_id', $parent->id)
                            ->where('parent_membership.role', 'parent')
                            ->where('parent_membership.status', 'active');
                    });
            })
            ->select('users.id');

        $attendance = Attendance::query()
            ->select('attendances.*')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->join('users as attendance_students', 'attendance_students.id', '=', 'attendances.student_id')
            ->whereIn('attendances.student_id', $childIds)
            ->where('attendance_students.status', 'active')
            ->whereNull('attendance_students.deleted_at')
            ->where('classrooms.status', 'active')
            ->where('academies.status', 'active')
            ->whereExists(function ($parentMembership) use ($parent): void {
                $parentMembership->selectRaw('1')
                    ->from('academy_user as memberships')
                    ->whereColumn('memberships.academy_id', 'classrooms.academy_id')
                    ->where('memberships.user_id', $parent->id)
                    ->where('memberships.role', 'parent')
                    ->where('memberships.status', 'active');
            })
            ->whereExists(function ($studentMembership): void {
                $studentMembership->selectRaw('1')
                    ->from('academy_user as memberships')
                    ->whereColumn('memberships.academy_id', 'classrooms.academy_id')
                    ->whereColumn('memberships.user_id', 'attendances.student_id')
                    ->where('memberships.role', 'student')
                    ->where('memberships.status', 'active');
            })
            ->whereExists(function ($classroomMembership): void {
                $classroomMembership->selectRaw('1')
                    ->from('classroom_student as memberships')
                    ->whereColumn('memberships.classroom_id', 'attendances.classroom_id')
                    ->whereColumn('memberships.student_id', 'attendances.student_id')
                    ->where('memberships.status', 'active');
            })
            ->with(['student:id,name', 'classroom:id,title,course_id', 'classroom.course:id,title'])
            ->orderByDesc('attendances.attendance_date')
            ->orderByDesc('attendances.id')
            ->paginate(20)
            ->withQueryString();

        return view('parent.attendance.index', compact('attendance'));
    }
}
