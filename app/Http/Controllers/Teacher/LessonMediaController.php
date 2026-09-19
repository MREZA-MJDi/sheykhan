<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\LessonMediaRequest;
use App\Models\Lesson;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;

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

        $media->upload(
            $request->file('media'),
            $lesson,
            [
                'disk' => 'local',
                'directory' => 'lessons/' . $lesson->id,
                'collection' => $request->string('collection')->toString() ?: 'lesson-assets',
                'visibility' => 'private',
            ]
        );

        return back()->with('success', 'محتوای درس آپلود شد.');
    }
}
