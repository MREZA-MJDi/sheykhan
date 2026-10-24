<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Classroom;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Course;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class LearningResourceController extends Controller
{
    public function index(
        Academy $academy,
        Request $request,
        OwnerWorkspaceService $workspace
    ): View {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        return view('owner.resources.index', [
            'academy' => $academy,
            'resources' => LearningResource::query()
                ->where('academy_id', $academy->id)
                ->with(['media:id,original_name,mime_type,size', 'course:id,title', 'classroom:id,title', 'lesson:id,title'])
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->paginate(20),
            'courses' => Course::query()
                ->where('academy_id', $academy->id)
                ->orderBy('title')
                ->get(['id', 'title']),
            'classrooms' => Classroom::query()
                ->where('academy_id', $academy->id)
                ->orderBy('title')
                ->get(['id', 'title', 'course_id']),
            'lessons' => Lesson::query()
                ->whereHas('section.course', fn ($query) => $query->where('academy_id', $academy->id))
                ->with('section.course:id,title')
                ->orderBy('title')
                ->get(['id', 'title', 'course_section_id']),
        ]);
    }

    public function store(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:204800', 'mimes:pdf,mp4,webm,mov,mp3,m4a,jpg,jpeg,png,webp,doc,docx,ppt,pptx,xls,xlsx,zip'],
            'course_id' => ['nullable', 'integer'],
            'classroom_id' => ['nullable', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
            'release_at' => ['nullable', 'date'],
        ]);

        $course = null;
        if (! empty($data['course_id'])) {
            $course = Course::query()
                ->where('academy_id', $academy->id)
                ->whereKey((int) $data['course_id'])
                ->firstOrFail();
        }

        $classroom = null;
        if (! empty($data['classroom_id'])) {
            $classroom = Classroom::query()
                ->where('academy_id', $academy->id)
                ->whereKey((int) $data['classroom_id'])
                ->firstOrFail();

            if ($course && (int) $classroom->course_id !== (int) $course->id) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'کلاس انتخاب‌شده به دورهٔ انتخابی تعلق ندارد.',
                ]);
            }

            $course ??= Course::query()
                ->where('academy_id', $academy->id)
                ->whereKey($classroom->course_id)
                ->firstOrFail();
        }

        $lesson = null;
        if (! empty($data['lesson_id'])) {
            $lesson = Lesson::query()
                ->whereHas('section.course', fn ($query) => $query->where('academy_id', $academy->id))
                ->whereKey((int) $data['lesson_id'])
                ->with('section.course:id,academy_id,id')
                ->firstOrFail();

            $lessonCourseId = (int) $lesson->section->course_id;
            if ($course && (int) $course->id !== $lessonCourseId) {
                throw ValidationException::withMessages([
                    'lesson_id' => 'درس انتخاب‌شده به دورهٔ انتخابی تعلق ندارد.',
                ]);
            }

            $course ??= Course::query()
                ->where('academy_id', $academy->id)
                ->whereKey($lessonCourseId)
                ->firstOrFail();
        }

        $file = $data['file'];
        $uploaded = $media->upload($file, null, [
            'disk' => 'local',
            'directory' => 'academy-resources/' . $academy->id,
            'collection' => 'learning-resources',
            'visibility' => 'private',
        ]);

        try {
            DB::transaction(function () use ($request, $academy, $course, $classroom, $lesson, $data, $uploaded): void {
                LearningResource::query()->create([
                    'academy_id' => $academy->id,
                    'course_id' => $course?->id,
                    'classroom_id' => $classroom?->id,
                    'lesson_id' => $lesson?->id,
                    'media_id' => $uploaded->id,
                    'uploaded_by' => $request->user()->id,
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'resource_type' => $this->resourceType((string) $uploaded->mime_type),
                    'visibility' => 'enrolled_students',
                    'release_at' => $data['release_at'] ?? null,
                    'downloadable' => false,
                    'status' => 'active',
                    'sort_order' => 0,
                ]);
            });
        } catch (Throwable $exception) {
            $media->delete($uploaded);
            throw $exception;
        }

        return redirect()
            ->route('owner.resources.index', $academy)
            ->with('success', 'فایل به‌صورت خصوصی ثبت شد و فقط به دانش‌آموزان دارای دسترسی نمایش داده می‌شود.');
    }


    public function edit(
        Academy $academy,
        LearningResource $resource,
        Request $request,
        OwnerWorkspaceService $workspace
    ): View {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $resource->academy_id === (int) $academy->id, 404);

        return view('owner.resources.edit', [
            'academy' => $academy,
            'resource' => $resource->load(['media:id,original_name,mime_type,size', 'course:id,title', 'classroom:id,title', 'lesson:id,title']),
            'courses' => Course::query()->where('academy_id', $academy->id)->orderBy('title')->get(['id', 'title']),
            'classrooms' => Classroom::query()->where('academy_id', $academy->id)->orderBy('title')->get(['id', 'title', 'course_id']),
            'lessons' => Lesson::query()
                ->whereHas('section.course', fn ($query) => $query->where('academy_id', $academy->id))
                ->with('section.course:id,title')
                ->orderBy('title')
                ->get(['id', 'title', 'course_section_id']),
        ]);
    }

    public function update(
        Request $request,
        Academy $academy,
        LearningResource $resource,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $resource->academy_id === (int) $academy->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:204800', 'mimes:pdf,mp4,webm,mov,mp3,m4a,jpg,jpeg,png,webp,doc,docx,ppt,pptx,xls,xlsx,zip'],
            'course_id' => ['nullable', 'integer'],
            'classroom_id' => ['nullable', 'integer'],
            'lesson_id' => ['nullable', 'integer'],
            'release_at' => ['nullable', 'date'],
        ]);

        $course = null;
        if (! empty($data['course_id'])) {
            $course = Course::query()->where('academy_id', $academy->id)->whereKey((int) $data['course_id'])->firstOrFail();
        }

        $classroom = null;
        if (! empty($data['classroom_id'])) {
            $classroom = Classroom::query()->where('academy_id', $academy->id)->whereKey((int) $data['classroom_id'])->firstOrFail();
            if ($course && (int) $classroom->course_id !== (int) $course->id) {
                throw ValidationException::withMessages(['classroom_id' => 'کلاس انتخاب‌شده به دورهٔ انتخابی تعلق ندارد.']);
            }
            $course ??= Course::query()->where('academy_id', $academy->id)->whereKey($classroom->course_id)->firstOrFail();
        }

        $lesson = null;
        if (! empty($data['lesson_id'])) {
            $lesson = Lesson::query()
                ->whereHas('section.course', fn ($query) => $query->where('academy_id', $academy->id))
                ->whereKey((int) $data['lesson_id'])
                ->with('section.course:id,academy_id,id')
                ->firstOrFail();
            $lessonCourseId = (int) $lesson->section->course_id;
            if ($course && (int) $course->id !== $lessonCourseId) {
                throw ValidationException::withMessages(['lesson_id' => 'درس انتخاب‌شده به دورهٔ انتخابی تعلق ندارد.']);
            }
            $course ??= Course::query()->where('academy_id', $academy->id)->whereKey($lessonCourseId)->firstOrFail();
        }

        if ($classroom && $lesson && (int) $classroom->course_id !== (int) $lesson->section->course_id) {
            throw ValidationException::withMessages(['lesson_id' => 'درس و کلاس انتخاب‌شده باید متعلق به یک دوره باشند.']);
        }

        $previousMedia = $resource->media;
        $uploaded = null;
        try {
            if (! empty($data['file'])) {
                $uploaded = $media->upload($data['file'], null, [
                    'disk' => 'local',
                    'directory' => 'academy-resources/' . $academy->id,
                    'collection' => 'learning-resources',
                    'visibility' => 'private',
                ]);
            }

            DB::transaction(function () use ($request, $academy, $resource, $course, $classroom, $lesson, $data, $uploaded): void {
                $locked = LearningResource::query()
                    ->where('academy_id', $academy->id)
                    ->whereKey($resource->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->fill([
                    'course_id' => $course?->id,
                    'classroom_id' => $classroom?->id,
                    'lesson_id' => $lesson?->id,
                    'uploaded_by' => $request->user()->id,
                    'title' => trim($data['title']),
                    'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                    'release_at' => $data['release_at'] ?? null,
                ]);
                if ($uploaded) {
                    $locked->media_id = $uploaded->id;
                    $locked->resource_type = $this->resourceType((string) $uploaded->mime_type);
                }
                $locked->save();
            });
        } catch (Throwable $exception) {
            if ($uploaded) {
                try {
                    if (! LearningResource::query()->where('media_id', $uploaded->id)->exists()
                        && ! $uploaded->attachments()->exists()) {
                        $media->delete($uploaded);
                    }
                } catch (Throwable $cleanupException) {
                    report(new \RuntimeException('A rolled-back learning-resource upload needs storage cleanup.', 0, $cleanupException));
                }
            }
            throw $exception;
        }

        if ($uploaded && $previousMedia && (int) $previousMedia->id !== (int) $uploaded->id) {
            try {
                if (! LearningResource::query()->where('media_id', $previousMedia->id)->exists()
                    && $previousMedia->attachments()->doesntExist()) {
                    $media->delete($previousMedia);
                }
            } catch (Throwable $cleanupException) {
                report(new \RuntimeException('A replaced learning resource could not be cleaned up.', 0, $cleanupException));
            }
        }

        return redirect()->route('owner.resources.edit', [$academy, $resource])
            ->with('success', 'مشخصات فایل ذخیره شد؛ فایل جدید خصوصی است و دسترسی آن همچنان بر اساس ثبت‌نام و زمان انتشار کنترل می‌شود.');
    }

    public function archive(
        Request $request,
        Academy $academy,
        LearningResource $resource,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $resource->academy_id === (int) $academy->id, 404);

        $resource->forceFill(['status' => 'inactive'])->save();

        return redirect()->route('owner.resources.index', $academy)
            ->with('success', 'منبع بایگانی شد؛ سوابق و فایل اصلی برای حفظ تاریخچه پاک نشدند.');
    }

    public function restore(
        Request $request,
        Academy $academy,
        LearningResource $resource,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $resource->academy_id === (int) $academy->id, 404);
        $mediaRecord = $resource->media;
        abort_unless(
            $mediaRecord
                && $mediaRecord->status === 'active'
                && filled($mediaRecord->path)
                && \Illuminate\Support\Facades\Storage::disk($mediaRecord->disk)->exists($mediaRecord->path),
            422,
            'فایل منبع در دسترس نیست؛ ابتدا فایل معتبر را دوباره بارگذاری کن.'
        );

        $resource->forceFill(['status' => 'active'])->save();

        return redirect()->route('owner.resources.index', $academy)
            ->with('success', 'منبع دوباره فعال شد.');
    }

    private function resourceType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            str_starts_with($mimeType, 'image/') => 'image',
            $mimeType === 'application/pdf' => 'document',
            $mimeType === 'application/zip' => 'archive',
            default => 'document',
        };
    }
}
