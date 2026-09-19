<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TeacherWorkspaceService;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function show(
        User $student,
        TeacherWorkspaceService $workspace
    ): View {
        abort_unless($student->hasRole('student'), 404);

        return view('teacher.students.show', $workspace->studentDetails(
            request()->user(),
            $student
        ));
    }
}
