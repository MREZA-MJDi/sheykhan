<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonNote;
use App\Services\StudentAccessService;
use App\Services\StudentCourseService;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function show(Lesson $lesson, StudentAccessService $access, StudentCourseService $courses): View
    {
        abort_unless($access->lesson(request()->user(), $lesson), 404);

        $lesson->loadMissing([
            'section.course:id,academy_id,title,slug,spotplayer_course_id',
            'media' => fn ($query) => $query
                ->where('status', 'active')
                ->orderBy('media_attachments.sort_order'),
        ]);

        $course = $lesson->section->course;
        $lessons = $courses->accessibleLessons(request()->user(), $course);
        $progress = $courses->progressByLesson(request()->user(), $lessons->pluck('id'));
        $currentIndex = $lessons->search(fn ($item) => $item->id === $lesson->id);

        $previous = $currentIndex !== false && $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $next = $currentIndex !== false && $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        $lesson->student_progress = (float) ($progress->get($lesson->id)->progress_percent ?? 0);
        $lesson->seconds_watched = (int) ($progress->get($lesson->id)->seconds_watched ?? 0);

        $note = LessonNote::query()
            ->where('lesson_id', $lesson->id)
            ->where('user_id', request()->user()->id)
            ->first();

        return view('student.lessons.show', [
            'lesson' => $lesson,
            'course' => $course,
            'lessons' => $lessons,
            'previousLesson' => $previous,
            'nextLesson' => $next,
            'note' => $note,
        ]);
    }
}
