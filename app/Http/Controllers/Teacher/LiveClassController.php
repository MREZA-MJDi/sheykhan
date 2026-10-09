<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLiveClassRequest;
use App\Models\LiveClass;
use App\Services\TeacherWorkspaceService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use Throwable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LiveClassController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.live-classes.index', [
            'liveClasses' => $workspace->liveClasses(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.live-classes.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'liveClass' => new LiveClass(['duration_minutes' => 60, 'status' => 'scheduled']),
        ]);
    }

    public function uploadRecording(
        Request $request,
        LiveClass $liveClass,
        TeacherWorkspaceService $workspace,
        MediaService $media,
    ): RedirectResponse {
        abort_unless((int) $liveClass->teacher_id === (int) $request->user()->id, 404);

        $workspace->courseOwnedBy($request->user(), (int) $liveClass->course_id);

        $data = $request->validate([
            'recording' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-matroska', 'max:204800'],
            'release_at' => ['nullable', 'date', 'after_or_equal:now'],
        ]);

        $newMedia = $media->upload($data['recording'], null, [
            'disk' => 'local',
            'directory' => 'live-class-recordings/' . $liveClass->id,
            'collection' => 'recordings',
            'visibility' => 'private',
        ]);

        $previousMediaId = $liveClass->recording_media_id;

        try {
            DB::transaction(function () use ($liveClass, $newMedia, $data): void {
                $liveClass->forceFill([
                    'recording_media_id' => $newMedia->id,
                    'recording_released_at' => isset($data['release_at'])
                        ? Carbon::parse($data['release_at'])
                        : now(),
                    'recording_visibility' => 'enrolled_students',
                    'ended_at' => $liveClass->ended_at ?: now(),
                    'status' => 'completed',
                ])->save();
            });
        } catch (Throwable $exception) {
            $media->delete($newMedia);
            throw $exception;
        }

        if ($previousMediaId && (int) $previousMediaId !== (int) $newMedia->id) {
            $previous = Media::query()->find($previousMediaId);
            if ($previous && $previous->attachments()->doesntExist()
                && ! LiveClass::query()->where('recording_media_id', $previous->id)->exists()) {
                $media->delete($previous);
            }
        }

        return back()->with('success', 'ضبط جلسه ذخیره شد؛ فقط دانش‌آموزان مجاز این دوره پس از زمان انتشار به آن دسترسی دارند.');
    }

    public function store(
        StoreLiveClassRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse($request->user(), (int) $classroomId, (int) $request->validated('course_id'));
        }

        $payload = $request->safe()->except('course_id');
        $payload['teacher_id'] = $request->user()->id;
        $payload['status'] = 'scheduled';

        $scheduledAt = Carbon::parse($payload['scheduled_at']);
        $payload['scheduled_end_at'] = $scheduledAt
            ->copy()
            ->addMinutes((int) $payload['duration_minutes']);

        $course->liveClasses()->create($payload);

        return redirect()->route('teacher.live-classes.index')
            ->with('success', 'جلسه آنلاین ثبت شد.');
    }
}
