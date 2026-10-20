<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerAssignmentRequest;
use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\MediaService;
use App\Services\OwnerAssignmentService;
use App\Services\TeacherAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AssignmentController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerAssignmentService $assignments): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'closed'])],
            'classroom_id' => ['nullable', 'integer', Rule::exists('classrooms', 'id')->where('academy_id', $academy->id)],
        ]);

        return view('owner.assignments.index', $assignments->index($request->user(), $academy, $filters));
    }

    public function create(Academy $academy, Request $request, OwnerAssignmentService $assignments): View
    {
        return view('owner.assignments.form', [
            ...$assignments->formData($request->user(), $academy),
            'assignment' => new Assignment(['status' => 'draft', 'max_score' => 20]),
        ]);
    }

    public function store(
        StoreOwnerAssignmentRequest $request,
        Academy $academy,
        OwnerAssignmentService $assignments,
        MediaService $media
    ): RedirectResponse {
        $assignment = $assignments->create(
            $request->user(),
            $academy,
            $request->validated(),
            $request->file('handout'),
            $media
        );

        return redirect()
            ->route('owner.assignments.show', [$academy, $assignment])
            ->with('success', 'تکلیف برای کلاس انتخاب‌شده ثبت شد.');
    }

    public function show(
        Academy $academy,
        Assignment $assignment,
        Request $request,
        OwnerAssignmentService $assignments
    ): View {
        return view('owner.assignments.show', $assignments->show($request->user(), $academy, $assignment));
    }

    public function grade(
        \App\Http\Requests\Teacher\GradeAssignmentSubmissionRequest $request,
        Academy $academy,
        Assignment $assignment,
        AssignmentSubmission $submission,
        OwnerAssignmentService $assignments,
        TeacherAssessmentService $assessment
    ): RedirectResponse {
        $assignments->assertAssignmentOwned($request->user(), $academy, $assignment);
        abort_unless((int) $submission->assignment_id === (int) $assignment->id, 404);

        $assessment->gradeAssignment(
            $assignment,
            $submission,
            (float) $request->validated('score'),
            $request->validated('feedback'),
            (int) $request->user()->id
        );

        return redirect()
            ->route('owner.assignments.show', [$academy, $assignment])
            ->with('success', 'نمره و بازخورد ذخیره شد.');
    }
}
