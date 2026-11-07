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


}
