<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Services\MediaService;
use App\Services\StudentAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(StudentAssignmentService $assignments): View
    {
        return view('student.assignments.index', [
            'assignments' => $assignments->query(request()->user())->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Assignment $assignment, StudentAssignmentService $assignments): View
    {
        return view('student.assignments.show', [
            'assignment' => $assignments->find(request()->user(), $assignment),
        ]);
    }

    public function submit(
        SubmitAssignmentRequest $request,
        Assignment $assignment,
        StudentAssignmentService $assignments,
        MediaService $media,
    ): RedirectResponse {
        $assignments->submit(
            $request->user(),
            $assignment,
            $request->validated(),
            $request->file('attachments', []),
            $media,
        );

        return redirect()->route('student.assignments.show', $assignment)
            ->with('success', 'پاسخ تکلیف با موفقیت ارسال شد.');
    }
}
