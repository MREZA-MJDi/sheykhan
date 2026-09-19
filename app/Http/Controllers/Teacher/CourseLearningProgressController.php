<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseLearningProgressService;
use Illuminate\View\View;

class CourseLearningProgressController extends Controller
{
    public function __invoke(
        Course $course,
        CourseLearningProgressService $progress
    ): View {
        return view('teacher.courses.progress', $progress->forTeacher(
            request()->user(),
            $course
        ));
    }
}
