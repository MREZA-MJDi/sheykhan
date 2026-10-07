<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentCourseService;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(StudentCourseService $courses): View
    {
        $coursesList = $courses->query(request()->user())->get();
        $progress = $courses->progressByCourse(request()->user(), $coursesList->pluck('id'));

        $coursesList->each(function (Course $course) use ($progress): void {
            $course->learning_progress = (float) ($progress[$course->id] ?? 0);
        });

        return view('student.courses.index', ['courses' => $coursesList]);
    }

    public function show(Course $course, StudentCourseService $courses): View
    {
        $course = $courses->find(request()->user(), $course);
        $lessons = $courses->accessibleLessons(request()->user(), $course);
        $progress = $courses->progressByLesson(request()->user(), $lessons->pluck('id'));

        $lessons->each(function ($lesson) use ($progress): void {
            $row = $progress->get($lesson->id);
            $lesson->student_progress = (float) ($row->progress_percent ?? 0);
            $lesson->seconds_watched = (int) ($row->seconds_watched ?? 0);
            $lesson->is_completed = $lesson->student_progress >= 100;
        });

        return view('student.courses.show', [
            'course' => $course,
            'lessons' => $lessons,
        ]);
    }
}
