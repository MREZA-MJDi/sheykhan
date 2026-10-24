<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CounselingRequestController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $requests = $this->visibleRequests($teacher)
            ->with(['academy:id,name', 'student:id,name', 'parent:id,name', 'proposer:id,name', 'handler:id,name'])
            ->orderByRaw("CASE WHEN status = 'requested' THEN 0 WHEN status = 'proposed' THEN 1 WHEN status = 'scheduled' THEN 2 ELSE 3 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.counseling.index', compact('requests'));
    }

    public function propose(Request $request, int $counselingRequest): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless($teacher->hasPermission('live_classes.manage'), 403);

        $data = $request->validate([
            'proposed_at' => ['required', 'date', 'after:now'],
            'proposal_notes' => ['nullable', 'string', 'max:3000'],
        ], [
            'proposed_at.required' => 'زمان پیشنهادی جلسه را مشخص کن.',
            'proposed_at.date' => 'زمان پیشنهادی معتبر نیست.',
            'proposed_at.after' => 'زمان پیشنهادی باید در آینده باشد.',
        ]);

        $record = $this->visibleRequests($teacher)->findOrFail($counselingRequest);
        abort_unless(in_array($record->status, ['requested', 'proposed'], true), 422);

        $record->forceFill([
            'status' => 'proposed',
            'proposed_at' => $data['proposed_at'],
            'proposed_by' => $teacher->id,
            'proposal_notes' => filled($data['proposal_notes'] ?? null) ? trim($data['proposal_notes']) : null,
        ])->save();

        return redirect()->route('teacher.counseling-requests.index')
            ->with('success', 'زمان پیشنهادی برای بررسی آموزشگاه ارسال شد.');
    }

    private function visibleRequests(User $teacher): Builder
    {
        return CounselingRequest::query()
            ->whereHas('academy', fn (Builder $academy) => $academy->where('academies.status', 'active'))
            ->whereHas('student.parents', fn (Builder $parents) => $parents
                ->whereColumn('users.id', 'counseling_requests.parent_id'))
            ->where(function (Builder $scope) use ($teacher): void {
                $scope->whereHas('student.classroomsAsStudent', fn (Builder $classrooms) => $classrooms
                    ->where('classrooms.status', 'active')
                    ->where('classroom_student.status', 'active')
                    ->whereColumn('classrooms.academy_id', 'counseling_requests.academy_id')
                    ->whereHas('teachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id))
                    ->whereHas('course.academy.users', fn (Builder $memberships) => $memberships
                        ->whereKey($teacher->id)
                        ->where('academy_user.role', 'teacher')
                        ->where('academy_user.status', 'active')))
                ->orWhereHas('student.enrollments', fn (Builder $enrollments) => $enrollments
                    ->where('course_enrollments.status', 'active')
                    ->whereHas('course', fn (Builder $courses) => $courses
                        ->whereColumn('courses.academy_id', 'counseling_requests.academy_id')
                        ->whereHas('teachers', fn (Builder $teachers) => $teachers->whereKey($teacher->id))
                        ->whereHas('academy.users', fn (Builder $memberships) => $memberships
                            ->whereKey($teacher->id)
                            ->where('academy_user.role', 'teacher')
                            ->where('academy_user.status', 'active'))));
            });
    }
}
