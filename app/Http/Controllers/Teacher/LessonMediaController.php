<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\LessonMediaRequest;
use App\Models\Lesson;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LessonMediaController extends Controller
{
    public function store(
        LessonMediaRequest $request,
        Lesson $lesson,
        MediaService $media
    ): RedirectResponse {
        $lesson->loadMissing('section.course');

        abort_unless(
            $lesson->section?->course?->teachers()->whereKey($request->user()->id)->exists(),
            403
        );

        $validated = $request->validated();

        $media->upload(
            $request->file('media'),
            $lesson,
            [
                'disk' => 'local',
                'directory' => 'lessons/' . $lesson->id,
                'collection' => $validated['collection'],
                'visibility' => 'private',
                'sort_order' => (int) ($validated['sort_order'] ?? 0),
                'metadata' => [
                    'access' => $validated['access'],
                    'downloadable' => (bool) ($validated['downloadable'] ?? $validated['collection'] !== 'video'),
                ],
            ]
        );

        return back()->with('success', 'محتوای درس با موفقیت آپلود شد.');
    }

    public function destroy(
        Lesson $lesson,
        Media $media,
        MediaService $mediaService
    ): RedirectResponse {
        $lesson->loadMissing('section.course');

        abort_unless(
            $lesson->section?->course?->teachers()->whereKey(request()->user()->id)->exists(),
            403
        );

        $attached = $media->attachments()
            ->where('mediable_type', Lesson::class)
            ->where('mediable_id', $lesson->id)
            ->exists();

        abort_unless($attached, 404);

        $mediaService->detach($media, $lesson);

        if ($media->attachments()->doesntExist()) {
            $mediaService->delete($media);
        }

        return back()->with('success', 'محتوای درس حذف شد.');
    }

    public function stream(
        Lesson $lesson,
        Media $media,
        MediaService $mediaService
    ): Response {
        $lesson->loadMissing('section.course');

        abort_unless(
            $lesson->section?->course?->teachers()->whereKey(request()->user()->id)->exists(),
            403
        );

        $attached = $media->attachments()
            ->where('mediable_type', Lesson::class)
            ->where('mediable_id', $lesson->id)
            ->exists();

        abort_unless($attached && $media->status === 'active', 404);

        return $mediaService->stream($media);
    }
}
