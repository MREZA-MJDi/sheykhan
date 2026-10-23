<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademyMessage;
use App\Models\ParentMessage;
use App\Models\TeacherDirectMessage;
use App\Models\User;
use App\Services\ParentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final class MessageController extends Controller
{
    public function index(Request $request, ParentPortalService $portal): View
    {
        $parent = $request->user();
        $parentAcademyIds = $parent->academies()
            ->where('academies.status', 'active')
            ->wherePivot('role', 'parent')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        $academyMessages = AcademyMessage::query()
            ->whereIn('academy_id', $parentAcademyIds)
            ->where(function (Builder $visible) use ($parent): void {
                $visible->where('audience', 'all_parents')
                    ->orWhere(fn (Builder $direct) => $direct
                        ->where('audience', 'parent')
                        ->where('recipient_id', $parent->id))
                    ->orWhere(function (Builder $classroomNotice) use ($parent): void {
                        $classroomNotice->where('audience', 'classroom_parents')
                            ->whereExists(function ($membership) use ($parent): void {
                                $membership->selectRaw('1')
                                    ->from('classroom_student as cs')
                                    ->join('classrooms as cr', 'cr.id', '=', 'cs.classroom_id')
                                    ->join('academy_user as student_membership', function ($join): void {
                                        $join->on('student_membership.user_id', '=', 'cs.student_id')
                                            ->on('student_membership.academy_id', '=', 'cr.academy_id')
                                            ->where('student_membership.role', '=', 'student')
                                            ->where('student_membership.status', '=', 'active');
                                    })
                                    ->join('parent_student as parent_link', 'parent_link.student_id', '=', 'cs.student_id')
                                    ->whereColumn('cs.classroom_id', 'academy_messages.classroom_id')
                                    ->whereColumn('cr.academy_id', 'academy_messages.academy_id')
                                    ->where('parent_link.parent_id', $parent->id)
                                    ->where('cs.status', 'active')
                                    ->where('cr.status', 'active');
                            });
                    });
            })
            ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'academy:id,name', 'classroom:id,title', 'course:id,title'])
            ->latest()
            ->paginate(10, ['*'], 'academy_messages_page')
            ->withQueryString();

        $teacherMessages = TeacherDirectMessage::query()
            ->where('recipient_type', 'parent')
            ->where('recipient_id', $request->user()->id)
            ->with(['teacher:id,name', 'student:id,name', 'course:id,title', 'classroom:id,title'])
            ->latest()
            ->paginate(10, ['*'], 'teacher_messages_page')
            ->withQueryString();

        $messages = ParentMessage::query()
            ->where('parent_id', $request->user()->id)
            ->with(['student:id,name', 'academy:id,name', 'responder:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $outgoingTeacherMessages = TeacherDirectMessage::query()
            ->where('sender_type', 'parent')
            ->where('sender_id', $request->user()->id)
            ->with(['teacher:id,name', 'student:id,name', 'course:id,title', 'classroom:id,title', 'responder:id,name'])
            ->latest()
            ->paginate(10, ['*'], 'outgoing_teacher_messages_page')
            ->withQueryString();

        return view('parent.messages.index', [
            'messages' => $messages,
            'contactableChildren' => $portal->contactableChildren($request->user()),
            'academyMessages' => $academyMessages,
            'teacherTargets' => $portal->contactableTeacherTargets($request->user()),
            'teacherMessages' => $teacherMessages,
            'outgoingTeacherMessages' => $outgoingTeacherMessages,
        ]);
    }


    public function storeToTeacher(Request $request, ParentPortalService $portal): RedirectResponse
    {
        $data = $request->validate([
            'teacher_target' => ['required', 'string', 'regex:/^classroom:\d+:\d+:\d+$/'],
            'teacher_category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'teacher_subject' => ['required', 'string', 'min:3', 'max:180'],
            'teacher_body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'teacher_target.required' => 'فرزند، کلاس و مدرس مرتبط را انتخاب کنید.',
            'teacher_subject.required' => 'موضوع پیام را وارد کنید.',
            'teacher_body.required' => 'متن پیام را وارد کنید.',
        ]);

        $parent = $request->user();
        $target = $portal->contactableTeacherTargets($parent)->firstWhere('key', $data['teacher_target']);
        abort_unless($target, 404, 'مدرس انتخاب‌شده به کلاس فعال فرزند شما مرتبط نیست.');

        TeacherDirectMessage::query()->create([
            'academy_id' => $target->academy_id,
            'teacher_id' => $target->teacher_id,
            'sender_id' => $parent->id,
            'sender_type' => 'parent',
            'recipient_type' => 'teacher',
            'recipient_id' => $target->teacher_id,
            'student_id' => $target->student_id,
            'course_id' => $target->course_id,
            'classroom_id' => $target->classroom_id,
            'category' => $data['teacher_category'],
            'subject' => trim($data['teacher_subject']),
            'body' => trim($data['teacher_body']),
            'status' => 'sent',
        ]);

        return redirect()->route('parent.messages.index')
            ->with('success', 'پیام شما برای مدرس همین کلاس ارسال شد.');
    }

    public function replyToTeacherMessage(Request $request, int $message): RedirectResponse
    {
        $data = $request->validate([
            'response' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'response.required' => 'متن پاسخ را وارد کنید.',
            'response.min' => 'پاسخ باید حداقل دو کاراکتر داشته باشد.',
        ]);

        $record = TeacherDirectMessage::query()
            ->whereKey($message)
            ->where('recipient_type', 'parent')
            ->where('recipient_id', $request->user()->id)
            ->where('status', 'sent')
            ->firstOrFail();

        $record->forceFill([
            'response' => trim($data['response']),
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
            'status' => 'responded',
        ])->save();

        return redirect()->route('parent.messages.index')
            ->with('success', 'پاسخ شما برای مدرس ثبت شد.');
    }

    public function store(Request $request, ParentPortalService $portal): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^\d+:\d+$/'],
            'category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'فرزند و آموزشگاه مرتبط را انتخاب کنید.',
            'target.regex' => 'انتخاب فرزند یا آموزشگاه معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کنید.',
            'body.required' => 'متن پیام را وارد کنید.',
        ]);

        [$studentId, $academyId] = array_map('intval', explode(':', $data['target'], 2));
        [$student, $academy] = $portal->resolveTarget($request->user(), $studentId, $academyId);

        ParentMessage::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $request->user()->id,
            'student_id' => $student->id,
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'status' => 'open',
        ]);

        return redirect()->route('parent.messages.index')
            ->with('success', 'پیام شما ثبت شد و برای پیگیری به آموزشگاه ارسال شد.');
    }
}
