<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\StoreCourseRequest;
use App\Http\Requests\Owner\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(CourseManagementService $service): View
    {
        return view('teacher.courses.index', [
            'courses' => $service->coursesFor(request()->user()),
        ]);
    }

    public function create(CourseManagementService $service): View
    {
        return view('teacher.courses.form', [
            'course' => new Course(),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function store(StoreCourseRequest $request, CourseManagementService $service): RedirectResponse
    {
        $course = $service->create($request->user(), $request->validated());

        return redirect()->route('teacher.courses.edit', $course)
            ->with('success', 'دوره با موفقیت ساخته شد.');
    }

    public function edit(Course $course, CourseManagementService $service): View
    {
        abort_unless($service->canManage(request()->user(), $course), 403);

        return view('teacher.courses.form', [
            'course' => $course->load('academy'),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course, CourseManagementService $service): RedirectResponse
    {
        $service->update($request->user(), $course, $request->validated());

        return redirect()->route('teacher.courses.edit', $course)
            ->with('success', 'دوره با موفقیت به‌روزرسانی شد.');
    }
}
