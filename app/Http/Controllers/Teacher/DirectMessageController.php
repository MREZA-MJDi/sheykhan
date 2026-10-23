<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\TeacherDirectMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class DirectMessageController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('parent_messages.manage'), 403);

        $messages = TeacherDirectMessage::query()
            ->where('teacher_id', $teacher->id)
            ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'course:id,title', 'classroom:id,title', 'responder:id,name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('teacher.direct-messages.index', [
            'targets' => $this->contactableTargets($teacher),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('parent_messages.manage'), 403);

        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^(student:\d+:\d+|parent:\d+:\d+:\d+)$/'],
            'category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'دانش‌آموز یا ولی مرتبط با کلاس خود را انتخاب کن.',
            'target.regex' => 'مخاطب انتخاب‌شده معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کن.',
            'body.required' => 'متن پیام را وارد کن.',
        ]);

        $parts = explode(':', $data['target']);
        $recipientType = array_shift($parts);
        $classroomId = (int) $parts[0];
        $studentId = (int) $parts[1];
        $parentId = $recipientType === 'parent' ? (int) $parts[2] : null;

        $classroom = Classroom::query()
            ->whereKey($classroomId)
            ->where('status', 'active')
            ->whereHas('teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
            ->whereHas('course.academy', fn ($academy) => $academy->where('status', 'active'))
            ->with('course:id,academy_id,title')
            ->firstOrFail();

        abort_unless(
            $teacher->academies()->whereKey($classroom->academy_id)
                ->wherePivot('role', 'teacher')->wherePivot('status', 'active')->exists(),
            404
        );

        $student = $classroom->students()
            ->whereKey($studentId)
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->firstOrFail();

        abort_unless(
            $student->academies()->whereKey($classroom->academy_id)
                ->wherePivot('role', 'student')->wherePivot('status', 'active')->exists(),
            404
        );

        $recipient = $student;
        if ($recipientType === 'parent') {
            $recipient = $student->parents()
                ->whereKey($parentId)
                ->where('users.status', 'active')
                ->firstOrFail();

            abort_unless(
                $recipient->academies()->whereKey($classroom->academy_id)
                    ->wherePivot('role', 'parent')->wherePivot('status', 'active')->exists(),
                404
            );
        }

        TeacherDirectMessage::query()->create([
            'academy_id' => $classroom->academy_id,
            'teacher_id' => $teacher->id,
            'sender_id' => $teacher->id,
            'sender_type' => 'teacher',
            'recipient_type' => $recipientType,
            'recipient_id' => $recipient->id,
            'student_id' => $student->id,
            'course_id' => $classroom->course_id,
            'classroom_id' => $classroom->id,
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'status' => 'sent',
        ]);

        return redirect()->route('teacher.direct-messages.index')
            ->with('success', 'پیام برای مخاطب انتخاب‌شده ارسال شد.');
    }

    public function replyToParentMessage(Request $request, int $message): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('parent_messages.manage'), 403);

        $data = $request->validate([
            'response' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'response.required' => 'متن پاسخ را وارد کن.',
            'response.min' => 'پاسخ باید حداقل دو کاراکتر داشته باشد.',
        ]);

        $record = TeacherDirectMessage::query()
            ->whereKey($message)
            ->where('teacher_id', $teacher->id)
            ->where('recipient_type', 'teacher')
            ->where('recipient_id', $teacher->id)
            ->where('sender_type', 'parent')
            ->where('status', 'sent')
            ->firstOrFail();

        $record->forceFill([
            'response' => trim($data['response']),
            'responded_by' => $teacher->id,
            'responded_at' => now(),
            'status' => 'responded',
        ])->save();

        return redirect()->route('teacher.direct-messages.index')
            ->with('success', 'پاسخ برای والد ثبت شد.');
    }

    /**
     * Options are derived only from active classroom membership and current teaching assignments.
     *
     * @return Collection<int, object>
     */
    private function contactableTargets(User $teacher): Collection
    {
        $base = DB::table('classroom_student as student_membership')
            ->join('classrooms', 'classrooms.id', '=', 'student_membership.classroom_id')
            ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'classrooms.id')
            ->join('courses', 'courses.id', '=', 'classrooms.course_id')
            ->join('users as students', 'students.id', '=', 'student_membership.student_id')
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
            ->where('teaching_assignment.teacher_id', $teacher->id)
            ->where('classrooms.status', 'active')
            ->where('student_membership.status', 'active')
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->distinct();

        $studentTargets = (clone $base)
            ->orderBy('classrooms.title')->orderBy('students.name')
            ->get([
                'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
                'classrooms.academy_id', 'courses.id as course_id', 'courses.title as course_title',
                'students.id as student_id', 'students.name as student_name',
            ])
            ->map(fn (object $row): object => (object) [
                'key' => 'student:' . $row->classroom_id . ':' . $row->student_id,
                'recipient_type' => 'student',
                'classroom_id' => $row->classroom_id,
                'classroom_title' => $row->classroom_title,
                'academy_id' => $row->academy_id,
                'course_id' => $row->course_id,
                'course_title' => $row->course_title,
                'student_id' => $row->student_id,
                'student_name' => $row->student_name,
                'recipient_id' => $row->student_id,
                'recipient_name' => $row->student_name,
            ]);

        $parentTargets = (clone $base)
            ->join('parent_student as parent_link', 'parent_link.student_id', '=', 'students.id')
            ->join('users as parents', 'parents.id', '=', 'parent_link.parent_id')
            ->join('academy_user as parent_membership', function ($join): void {
                $join->on('parent_membership.user_id', '=', 'parents.id')
                    ->on('parent_membership.academy_id', '=', 'classrooms.academy_id')
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->where('parents.status', 'active')
            ->whereNull('parents.deleted_at')
            ->orderBy('classrooms.title')->orderBy('students.name')->orderBy('parents.name')
            ->get([
                'classrooms.id as classroom_id', 'classrooms.title as classroom_title',
                'classrooms.academy_id', 'courses.id as course_id', 'courses.title as course_title',
                'students.id as student_id', 'students.name as student_name',
                'parents.id as parent_id', 'parents.name as parent_name',
            ])
            ->map(fn (object $row): object => (object) [
                'key' => 'parent:' . $row->classroom_id . ':' . $row->student_id . ':' . $row->parent_id,
                'recipient_type' => 'parent',
                'classroom_id' => $row->classroom_id,
                'classroom_title' => $row->classroom_title,
                'academy_id' => $row->academy_id,
                'course_id' => $row->course_id,
                'course_title' => $row->course_title,
                'student_id' => $row->student_id,
                'student_name' => $row->student_name,
                'recipient_id' => $row->parent_id,
                'recipient_name' => $row->parent_name,
            ]);

        return $studentTargets->concat($parentTargets)
            ->sortBy(fn (object $target): string => $target->classroom_title . ' ' . $target->student_name . ' ' . $target->recipient_name)
            ->values();
    }
}
