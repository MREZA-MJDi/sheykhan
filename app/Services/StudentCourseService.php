<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class StudentCourseService
{
    public function query(User $student): Builder
    {
        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student);

        return Course::query()
            ->whereIn('id', $courseIds)
            ->published()
            ->with([
                'academy:id,name',
                'teachers:id,name',
                'sections:id,course_id,title,sort_order',
                'sections.lessons:id,course_section_id,title,slug,type,summary,content,duration_seconds,is_free,status,published_at,sort_order',
            ])
            ->orderBy('title');
    }

    public function find(User $student, Course $course): Course
    {
        abort_unless(app(StudentAccessService::class)->course($student, $course), 404);

        return $course->load([
            'academy:id,name',
            'teachers:id,name',
            'sections:id,course_id,title,sort_order',
            'sections.lessons' => fn ($query) => $query
                ->where('status', 'published')
                ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
                ->select([
                    'id','course_section_id','title','slug','type','summary',
                    'content','duration_seconds','is_free','status','published_at','sort_order',
                ])
                ->orderBy('sort_order'),
        ]);
    }

    public function progressByCourse(User $student, $courseIds)
    {
        if ($courseIds->isEmpty()) {
            return collect();
        }

        return DB::table('course_sections')
            ->join('lessons', 'lessons.course_section_id', '=', 'course_sections.id')
            ->leftJoin('lesson_progress as progress', function ($join) use ($student): void {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                    ->where('progress.user_id', '=', $student->id);
            })
            ->whereIn('course_sections.course_id', $courseIds)
            ->where('lessons.status', 'published')
            ->where(fn ($query) => $query->whereNull('lessons.published_at')->orWhere('lessons.published_at', '<=', now()))
            ->groupBy('course_sections.course_id')
            ->select('course_sections.course_id')
            ->selectRaw('AVG(COALESCE(progress.progress_percent, 0)) as progress_average')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->course_id => (float) $row->progress_average]);
    }

    public function progressByLesson(User $student, $lessonIds)
    {
        if ($lessonIds->isEmpty()) {
            return collect();
        }

        return DB::table('lesson_progress')
            ->where('user_id', $student->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get([
                'lesson_id',
                'progress_percent',
                'seconds_watched',
                'completed_at',
                'last_watched_at',
            ])
            ->keyBy('lesson_id');
    }

    public function accessibleLessons(User $student, Course $course)
    {
        $course = $this->find($student, $course);

        return $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->values();
    }
}
