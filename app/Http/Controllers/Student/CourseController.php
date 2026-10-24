<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentCourseService;
use App\Services\SpotPlayerService;
use App\Models\SpotPlayerLicense;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(StudentCourseService $courses): View
    {
        $search = mb_substr(trim(request()->string('q')->toString()), 0, 100);
        $query = $courses->query(request()->user());

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('academy', fn ($academy) => $academy->where('name', 'like', '%' . $search . '%'));
            });
        }

        $coursesList = $query->paginate(12)->withQueryString();

        $progress = $courses->progressByCourse(
            request()->user(),
            $coursesList->getCollection()->pluck('id')
        );

        $coursesList->getCollection()->each(function (Course $course) use ($progress): void {
            $course->learning_progress = (float) ($progress[$course->id] ?? 0);
        });

        return view('student.courses.index', ['courses' => $coursesList, 'courseSearch' => $search]);
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

        $spotplayerLicense = SpotPlayerLicense::query()
            ->where('student_id', request()->user()->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->first();

        return view('student.courses.show', [
            'course' => $course,
            'lessons' => $lessons,
            'spotplayerLicense' => $spotplayerLicense,
            'spotplayerConfigured' => app(SpotPlayerService::class)->isConfigured(),
        ]);
    }
}
