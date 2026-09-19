<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TeacherCourseContentService
{
    public function assertTeacher(User $teacher, Course $course): void
    {
        if (!$course->teachers()->whereKey($teacher->id)->exists()) {
            throw new AccessDeniedHttpException('این دوره برای این مدرس قابل مدیریت نیست.');
        }
    }

    public function assertSectionOwner(User $teacher, CourseSection $section): Course
    {
        $course = $section->course()->firstOrFail();
        $this->assertTeacher($teacher, $course);

        return $course;
    }

    public function assertLessonOwner(User $teacher, Lesson $lesson): Course
    {
        $lesson->loadMissing('section.course');
        $course = $lesson->section?->course;

        abort_unless($course, 404);
        $this->assertTeacher($teacher, $course);

        return $course;
    }

    public function sectionsFor(User $teacher, Course $course): Collection
    {
        $this->assertTeacher($teacher, $course);

        return $course->sections()
            ->withCount([
                'lessons',
                'lessons as published_lessons_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->with([
                'lessons' => fn ($query) => $query
                    ->with(['media' => fn ($media) => $media
                        ->orderByPivot('sort_order')
                        ->orderBy('media.id')])
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function createSection(User $teacher, Course $course, array $data): CourseSection
    {
        $this->assertTeacher($teacher, $course);

        return DB::transaction(function () use ($course, $data) {
            $sortOrder = $data['sort_order'] ?? ((int) $course->sections()->max('sort_order') + 1);

            return $course->sections()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'sort_order' => $sortOrder,
            ]);
        });
    }

    public function updateSection(User $teacher, CourseSection $section, array $data): CourseSection
    {
        $this->assertSectionOwner($teacher, $section);

        $section->update([
            'title' => $data['title'] ?? $section->title,
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : $section->description,
            'sort_order' => $data['sort_order'] ?? $section->sort_order,
        ]);

        return $section->refresh();
    }

    public function deleteSection(User $teacher, CourseSection $section): void
    {
        $this->assertSectionOwner($teacher, $section);

        if ($section->lessons()->exists()) {
            throw new \LogicException('برای حذف سرفصل، ابتدا درس‌های آن را حذف یا جابه‌جا کنید.');
        }

        $section->delete();
    }

    public function reorderSections(User $teacher, Course $course, array $ids): void
    {
        $this->assertTeacher($teacher, $course);

        $allowed = $course->sections()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $ordered = array_values(array_unique(array_map('intval', $ids)));

        if (count($allowed) !== count($ordered) || array_diff($allowed, $ordered) || array_diff($ordered, $allowed)) {
            throw new \InvalidArgumentException('ترتیب سرفصل‌ها معتبر نیست.');
        }

        DB::transaction(function () use ($course, $ordered): void {
            foreach ($ordered as $index => $sectionId) {
                $course->sections()
                    ->whereKey($sectionId)
                    ->update(['sort_order' => $index]);
            }
        });
    }

    public function reorderLessons(User $teacher, CourseSection $section, array $ids): void
    {
        $this->assertSectionOwner($teacher, $section);

        $allowed = $section->lessons()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $ordered = array_values(array_unique(array_map('intval', $ids)));

        if (
            count($allowed) !== count($ordered)
            || array_diff($allowed, $ordered)
            || array_diff($ordered, $allowed)
        ) {
            throw new \InvalidArgumentException('ترتیب درس‌ها معتبر نیست.');
        }

        DB::transaction(function () use ($section, $ordered): void {
            foreach ($ordered as $index => $lessonId) {
                $section->lessons()
                    ->whereKey($lessonId)
                    ->update(['sort_order' => $index]);
            }
        });
    }

    public function createLesson(User $teacher, CourseSection $section, array $data): Lesson
    {
        $this->assertSectionOwner($teacher, $section);

        $payload = [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'type' => $data['type'] ?? 'video',
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'is_free' => (bool) ($data['is_free'] ?? false),
            'status' => $data['status'] ?? 'draft',
            'published_at' => ($data['status'] ?? 'draft') === 'published'
                ? ($data['published_at'] ?? now())
                : null,
            'sort_order' => $data['sort_order'] ?? ((int) $section->lessons()->max('sort_order') + 1),
        ];

        return $section->lessons()->create($payload);
    }

    public function updateLesson(User $teacher, Lesson $lesson, array $data): Lesson
    {
        $course = $this->assertLessonOwner($teacher, $lesson);
        $targetSectionId = (int) ($data['course_section_id'] ?? $lesson->course_section_id);

        $targetSection = CourseSection::query()
            ->whereKey($targetSectionId)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $status = $data['status'] ?? $lesson->status;

        $lesson->update([
            'course_section_id' => $targetSection->id,
            'title' => $data['title'] ?? $lesson->title,
            'slug' => $data['slug'] ?? $lesson->slug,
            'type' => $data['type'] ?? $lesson->type,
            'summary' => array_key_exists('summary', $data) ? $data['summary'] : $lesson->summary,
            'content' => array_key_exists('content', $data) ? $data['content'] : $lesson->content,
            'duration_seconds' => $data['duration_seconds'] ?? $lesson->duration_seconds,
            'is_free' => array_key_exists('is_free', $data) ? (bool) $data['is_free'] : $lesson->is_free,
            'status' => $status,
            'published_at' => $status === 'published'
                ? ($data['published_at'] ?? $lesson->published_at ?? now())
                : null,
            'sort_order' => $data['sort_order'] ?? $lesson->sort_order,
        ]);

        return $lesson->refresh();
    }

    public function deleteLesson(User $teacher, Lesson $lesson, MediaService $mediaService): void
    {
        $this->assertLessonOwner($teacher, $lesson);

        DB::transaction(function () use ($lesson, $mediaService): void {
            $lesson->load('media');

            foreach ($lesson->media as $media) {
                $mediaService->detach($media, $lesson);

                if ($media->attachments()->doesntExist()) {
                    $mediaService->delete($media);
                }
            }

            $lesson->delete();
        });
    }
}
