<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StudentMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = $this->visibleMessages($request->user())
            ->with([
                'student:id,name',
                'course:id,title',
                'classroom:id,title',
                'responder:id,name',
            ])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('teacher.student-messages.index', compact('messages'));
    }

    public function reply(Request $request, StudentMessage $message): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($this->visibleMessages($teacher)->whereKey($message->id)->exists(), 404);
        abort_unless($message->status !== 'closed', 422, 'این مکاتبه بسته شده و امکان پاسخ جدید ندارد.');

        $data = $request->validate([
            'response' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'response.required' => 'متن پاسخ را بنویسید.',
            'response.min' => 'پاسخ باید حداقل دو کاراکتر داشته باشد.',
        ]);

        $message->forceFill([
            'status' => 'responded',
            'response' => trim($data['response']),
            'responded_by' => $teacher->id,
            'responded_at' => now(),
        ])->save();

        return redirect()->route('teacher.student-messages.index')
            ->with('success', 'پاسخ شما ثبت شد و در کارتابل دانش‌آموز نمایش داده می‌شود.');
    }

    public function close(Request $request, StudentMessage $message): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($this->visibleMessages($teacher)->whereKey($message->id)->exists(), 404);
        abort_unless($message->status !== 'closed', 422, 'این مکاتبه قبلاً بسته شده است.');
        abort_unless(filled($message->response), 422, 'برای بستن مکاتبه، ابتدا پاسخ را ثبت کنید.');

        $message->forceFill(['status' => 'closed'])->save();

        return redirect()->route('teacher.student-messages.index')
            ->with('success', 'مکاتبه بسته شد؛ سابقه و پاسخ برای دانش‌آموز محفوظ می‌ماند.');
    }

    /**
     * Enforce current academy membership and the exact active teaching context.
     * A historical assignment must not keep exposing private messages after a
     * teacher has been removed from that classroom or course.
     *
     * @return Builder<StudentMessage>
     */
    private function visibleMessages(User $teacher): Builder
    {
        $activeAcademyIds = $teacher->academies()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->pluck('academies.id');

        return StudentMessage::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('academy_id', $activeAcademyIds)
            ->where(function (Builder $scope) use ($teacher): void {
                $scope->where(function (Builder $classroom) use ($teacher): void {
                    $classroom
                        ->whereNotNull('classroom_id')
                        ->whereHas('classroom', fn (Builder $record) => $record
                            ->where('status', 'active')
                            ->whereColumn('classrooms.academy_id', 'student_messages.academy_id')
                            ->whereColumn('classrooms.course_id', 'student_messages.course_id')
                            ->whereHas('teachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id)));
                })->orWhere(function (Builder $course) use ($teacher): void {
                    $course
                        ->whereNull('classroom_id')
                        ->whereNotNull('course_id')
                        ->whereHas('course', fn (Builder $record) => $record
                            ->where('status', 'published')
                            ->whereNotNull('published_at')
                            ->where('published_at', '<=', now())
                            ->whereColumn('courses.academy_id', 'student_messages.academy_id')
                            ->whereHas('teachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id)));
                });
            });
    }
}
