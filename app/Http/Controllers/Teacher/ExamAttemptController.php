<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\GradeExamAttemptRequest;
use App\Models\ExamAttempt;
use App\Services\TeacherAssessmentService;
use Illuminate\Http\RedirectResponse;

class ExamAttemptController extends Controller
{
    public function grade(
        ExamAttempt $attempt,
        TeacherAssessmentService $assessments
    ): RedirectResponse {
        $assessments->gradeExamAttemptAutomatically($attempt, request()->user()->id);

        return back()->with('success', 'نتیجه خودکار آزمون ثبت شد.');
    }

    public function gradeManual(
        GradeExamAttemptRequest $request,
        ExamAttempt $attempt,
        TeacherAssessmentService $assessments
    ): RedirectResponse {
        $assessments->gradeExamAttemptManually(
            $attempt,
            $request->validated('answers'),
            $request->user()->id
        );

        return back()->with('success', 'نمره‌های آزمون ثبت و نتیجه نهایی شد.');
    }
}
