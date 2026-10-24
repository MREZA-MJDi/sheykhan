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
            ->whereExists(function ($parentLink): void {
                $parentLink->selectRaw('1')
                    ->from('parent_student as parent_links')
                    ->whereColumn('parent_links.student_id', 'counseling_requests.student_id')
                    ->whereColumn('parent_links.parent_id', 'counseling_requests.parent_id');
            })
            ->where(function ($scope) use ($teacher): void {
                $scope->whereExists(function ($classroomScope) use ($teacher): void {
                    $classroomScope->selectRaw('1')
                        ->from('classroom_student as student_membership')
                        ->join('classrooms', 'classrooms.id', '=', 'student_membership.classroom_id')
                        ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'classrooms.id')
                        ->join('academy_user as teacher_membership', function ($join): void {
                            $join->on('teacher_membership.user_id', '=', 'teaching_assignment.teacher_id')
                                ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                                ->where('teacher_membership.role', '=', 'teacher')
                                ->where('teacher_membership.status', '=', 'active');
                        })
                        ->join('academy_user as student_academy_membership', function ($join): void {
                            $join->on('student_academy_membership.user_id', '=', 'student_membership.student_id')
                                ->on('student_academy_membership.academy_id', '=', 'classrooms.academy_id')
                                ->where('student_academy_membership.role', '=', 'student')
                                ->where('student_academy_membership.status', '=', 'active');
                        })
                        ->whereColumn('student_membership.student_id', 'counseling_requests.student_id')
                        ->whereColumn('classrooms.academy_id', 'counseling_requests.academy_id')
                        ->where('classrooms.status', 'active')
                        ->where('student_membership.status', 'active')
                        ->where('teaching_assignment.teacher_id', $teacher->id);
                })->orWhereExists(function ($courseScope) use ($teacher): void {
                    $courseScope->selectRaw('1')
                        ->from('course_enrollments as enrollments')
                        ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                        ->join('course_teacher as teaching_assignment', 'teaching_assignment.course_id', '=', 'courses.id')
                        ->join('academies', 'academies.id', '=', 'courses.academy_id')
                        ->join('academy_user as teacher_membership', function ($join): void {
                            $join->on('teacher_membership.user_id', '=', 'teaching_assignment.teacher_id')
                                ->on('teacher_membership.academy_id', '=', 'courses.academy_id')
                                ->where('teacher_membership.role', '=', 'teacher')
                                ->where('teacher_membership.status', '=', 'active');
                        })
                        ->join('academy_user as student_membership', function ($join): void {
                            $join->on('student_membership.user_id', '=', 'enrollments.student_id')
                                ->on('student_membership.academy_id', '=', 'courses.academy_id')
                                ->where('student_membership.role', '=', 'student')
                                ->where('student_membership.status', '=', 'active');
                        })
                        ->whereColumn('enrollments.student_id', 'counseling_requests.student_id')
                        ->whereColumn('courses.academy_id', 'counseling_requests.academy_id')
                        ->where('enrollments.status', 'active')
                        ->where('academies.status', 'active')
                        ->where('teaching_assignment.teacher_id', $teacher->id);
                });
            });
    }}
