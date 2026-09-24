<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\\Controller;
use App\Http\Requests\Owner\Course\\StoreCourseRequest;
use App\Http\Requests\Owner\Course\\UpdateCourseRequest;
use App\Models\\Course;
use App\Services\\CourseManagementService;
use Illuminate\Http\\RedirectResponse;
use Illuminate\View\\View;

class CourseController extends Controller
{
    public function index(CourseManagementService $service): View
    {
        return view('owner.courses.index', $service->ownerIndexData(request()->user()));
    }

    public function create(CourseManagementService $service): View
    {
        return view('owner.courses.form', [
            'course' => new Course(),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function store(StoreCourseRequest $request, CourseManagementService $service): RedirectResponse
    {
        $course = $service->create($request->user(), $request->validated());

        return redirect()->route('owner.courses.show', $course)
            ->with('success', 'دوره با موفقیت ساخته شد.');
    }

    public function show(Course $course, CourseManagementService $service): View
    {
        abort_unless($service->canManage(request()->user(), $course), 403);

        $course->load([
            'academy:id,name',
            'teachers:id,name',
            'sections.lessons',
            'media' => fn ($query) => $query->orderByPivot('sort_order'),
            'exams' => fn ($query) => $query
                ->with('teacher:id,name')
                ->withCount(['questions', 'attempts'])
                ->latest()
                ->limit(8),
            'assignments' => fn ($query) => $query
                ->with('teacher:id,name')
                ->withCount([
                    'submissions',
                    'submissions as pending_submissions_count' => fn ($submissions) => $submissions
                        ->whereNotNull('submitted_at')
                        ->whereNull('graded_at'),
                ])
                ->latest()
                ->limit(8),
        ]);

        $course->loadCount([
            'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
            'assignments',
            'exams',
            'liveClasses',
        ]);

        $averageProgress = (float) $course->enrollments()
            ->where('status', 'active')
            ->join('lesson_progress', 'lesson_progress.user_id', '=', 'course_enrollments.student_id')
            ->join('lessons', 'lessons.id', '=', 'lesson_progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->where('course_sections.course_id', $course->id)
            ->avg('lesson_progress.progress_percent');

        return view('owner.courses.show', [
            'course' => $course,
            'averageProgress' => round($averageProgress, 1),
        ]);
    }

    public function edit(Course $course, CourseManagementService $service): View
    {
        abort_unless($service->canManage(request()->user(), $course), 403);

        return view('owner.courses.form', [
            'course' => $course->load([
                'academy',
                'media' => fn ($query) => $query->orderByPivot('sort_order'),
            ]),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course, CourseManagementService $service): RedirectResponse
    {
        $service->update($request->user(), $course, $request->validated());

        return redirect()->route('owner.courses.show', $course)
            ->with('success', 'دوره با موفقیت به‌روزرسانی شد.');
    }
}