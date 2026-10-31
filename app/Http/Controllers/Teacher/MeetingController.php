<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademyMeeting;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $activeTeacherMembership = fn ($academy) => $academy
            ->where('academies.status', 'active')
            ->whereHas('users', fn ($memberships) => $memberships
                ->whereKey($teacher->id)
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active'));

        $meetings = AcademyMeeting::query()
            ->whereHas('classroom', fn ($classroom) => $classroom
                ->where('classrooms.status', 'active')
                ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
                ->whereHas('academy', $activeTeacherMembership))
            ->with(['academy:id,name', 'classroom:id,title', 'course:id,title', 'student:id,name', 'guardian:id,name'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        $targets = DB::table('classroom_student as student_membership')
            ->join('classrooms', 'classrooms.id', '=', 'student_membership.classroom_id')
            ->join('academies as active_academy', function ($join): void {
                $join->on('active_academy.id', '=', 'classrooms.academy_id')
                    ->where('active_academy.status', '=', 'active');
            })
            ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'classrooms.id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('users as students', 'students.id', '=', 'student_membership.student_id')
            ->join('course_enrollments as active_enrollment', function ($join): void {
                $join->on('active_enrollment.student_id', '=', 'students.id')
                    ->on('active_enrollment.classroom_id', '=', 'classrooms.id')
                    ->on('active_enrollment.course_id', '=', 'classrooms.course_id')
                    ->where('active_enrollment.status', '=', 'active');
            })
            ->join('academy_user as teacher_membership', function ($join): void {
                $join->on('teacher_membership.user_id', '=', 'teaching_assignment.teacher_id')
                    ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('teacher_membership.role', '=', 'teacher')
                    ->where('teacher_membership.status', '=', 'active');
            })
            ->join('academy_user as student_membership_active', function ($join): void {
                $join->on('student_membership_active.user_id', '=', 'students.id')
                    ->on('student_membership_active.academy_id', '=', 'classrooms.academy_id')
                    ->where('student_membership_active.role', '=', 'student')
                    ->where('student_membership_active.status', '=', 'active');
            })
            ->join('parent_student as parent_link', 'parent_link.student_id', '=', 'students.id')
            ->join('users as parents', 'parents.id', '=', 'parent_link.parent_id')
            ->join('academy_user as parent_membership', function ($join): void {
                $join->on('parent_membership.user_id', '=', 'parents.id')
                    ->on('parent_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->where('teaching_assignment.teacher_id', $teacher->id)
            ->where('classrooms.status', 'active')
            ->where('student_membership.status', 'active')
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->where(function ($entitlement): void {
                $entitlement->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('active_enrollment.payment_status', 'paid')
                            ->where('active_enrollment.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('active_enrollment.price_amount')
                                    ->orWhereColumn('active_enrollment.paid_amount', '>=', 'active_enrollment.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')
                                    ->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'active_enrollment.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->where('parents.status', 'active')
            ->whereNull('parents.deleted_at')
            ->orderBy('classrooms.title')->orderBy('students.name')->orderBy('parents.name')
            ->distinct()
            ->get([
                'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
                'courses.id as course_id', 'courses.title as course_title', 'classrooms.academy_id',
                'students.id as student_id', 'students.name as student_name',
                'parents.id as parent_id', 'parents.name as parent_name',
            ]);

        return view('teacher.meetings.index', compact('meetings', 'targets'));
    }


    public function propose(Request $request): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('live_classes.manage'), 403);

        $data = $request->validate([
            'meeting_target' => ['required', 'string', 'regex:/^\\d+:\\d+:\\d+$/'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_at'],
            'message' => ['nullable', 'string', 'max:1500'],
        ]);

        [$classroomId, $studentId, $parentId] = array_map('intval', explode(':', $data['meeting_target']));

        $classroom = Classroom::query()
            ->whereKey($classroomId)
            ->where('status', 'active')
            ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
            ->whereHas('course.academy', fn ($academy) => $academy->where('status', 'active'))
            ->firstOrFail();

        abort_unless($teacher->academies()->whereKey($classroom->academy_id)
            ->wherePivot('role', 'teacher')->wherePivot('status', 'active')->exists(), 404);

        $student = $classroom->students()
            ->whereKey($studentId)
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->firstOrFail();

        $hasEligibleEnrollment = DB::table('course_enrollments as enrollment')
            ->join('courses', 'courses.id', '=', 'enrollment.course_id')
            ->where('enrollment.student_id', $student->id)
            ->where('enrollment.course_id', $classroom->course_id)
            ->where('enrollment.classroom_id', $classroom->id)
            ->where('enrollment.status', 'active')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->where(function ($entitlement): void {
                $entitlement->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('enrollment.payment_status', 'paid')
                            ->where('enrollment.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('enrollment.price_amount')
                                    ->orWhereColumn('enrollment.paid_amount', '>=', 'enrollment.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')
                                    ->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'enrollment.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->exists();

        abort_unless($hasEligibleEnrollment, 404, 'ثبت‌نام فعال و مجاز دانش‌آموز در این دوره پیدا نشد.');

        abort_unless($student->academies()->whereKey($classroom->academy_id)
            ->wherePivot('role', 'student')->wherePivot('status', 'active')->exists(), 404);

        $parent = $student->parents()
            ->whereKey($parentId)
            ->where('users.status', 'active')
            ->firstOrFail();

        abort_unless($parent->academies()->whereKey($classroom->academy_id)
            ->wherePivot('role', 'parent')->wherePivot('status', 'active')->exists(), 404);

        AcademyMeeting::query()->create([
            'academy_id' => $classroom->academy_id,
            'classroom_id' => $classroom->id,
            'course_id' => $classroom->course_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'created_by' => $teacher->id,
            'title' => trim($data['title']),
            'message' => filled($data['message'] ?? null) ? trim($data['message']) : null,
            'scheduled_at' => $data['scheduled_at'],
            'scheduled_end_at' => $data['scheduled_end_at'] ?? null,
            'status' => 'proposed',
        ]);

        return redirect()->route('teacher.meetings.index')
            ->with('success', 'پیشنهاد زمان جلسه برای بررسی به کارتابل آموزشگاه ارسال شد.');
    }

    public function updateStatus(Request $request, int $meeting): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:completed,cancelled'],
        ]);

        $teacher = $request->user();
        $activeTeacherMembership = fn ($academy) => $academy
            ->where('academies.status', 'active')
            ->whereHas('users', fn ($memberships) => $memberships
                ->whereKey($teacher->id)
                ->wherePivot('role', 'teacher')
                ->wherePivot('status', 'active'));

        $record = AcademyMeeting::query()
            ->whereKey($meeting)
            ->where(function ($query) use ($teacher, $activeTeacherMembership): void {
                $query->whereHas('classroom', fn ($classroom) => $classroom
                    ->where('classrooms.status', 'active')
                    ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
                    ->whereHas('academy', $activeTeacherMembership))
                    ->orWhereHas('course', fn ($course) => $course
                        ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
                        ->whereHas('academy', $activeTeacherMembership));
            })
            ->firstOrFail();

        abort_unless($record->status === 'scheduled', 422, 'فقط جلسه‌های برنامه‌ریزی‌شده قابل تغییر هستند.');
        $record->forceFill(['status' => $data['status']])->save();

        return redirect()->route('teacher.meetings.index')
            ->with('success', $data['status'] === 'completed' ? 'جلسه به‌عنوان برگزارشده ثبت شد.' : 'جلسه لغو شد.');
    }
}
