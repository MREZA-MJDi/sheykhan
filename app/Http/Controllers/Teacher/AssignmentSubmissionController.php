<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GradeAssignmentSubmissionRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\TeacherAssessmentService;
use Illuminate\Http\RedirectResponse;

class AssignmentSubmissionController extends Controller
{
    public function update(
        GradeAssignmentSubmissionRequest $request,
        Assignment $assignment,
        AssignmentSubmission $submission,
        TeacherAssessmentService $assessments
    ): RedirectResponse {
        $assessments->gradeAssignment(
            $assignment,
            $submission,
            (float) $request->validated('score'),
            $request->validated('feedback'),
            $request->user()->id
        );

        return back()->with('success', 'تکلیف با موفقیت تصحیح شد.');
    }
}
