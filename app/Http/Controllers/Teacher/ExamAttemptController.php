<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\TeacherAssessmentService;
use Illuminate\Http\RedirectResponse;

class ExamAttemptController extends Controller
{
    public function grade(
        ExamAttempt $attempt,
        TeacherAssessmentService $assessments
    ): RedirectResponse {
        $assessments->gradeExamAttempt($attempt, request()->user()->id);

        return back()->with('success', 'آزمون با موفقیت بررسی و نتیجه ثبت شد.');
    }
}
