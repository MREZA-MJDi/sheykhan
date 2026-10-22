<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\LiveClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

final class RecordingController extends Controller
{
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'live_classes.view');

        $recordings = LiveClass::query()
            ->whereNotNull('recording_media_id')
            ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
            ->with([
                'course:id,academy_id,title',
                'classroom:id,title',
                'teacher:id,name',
                'recording:id,original_name,mime_type,size,status,visibility,path,disk',
            ])
            ->orderByDesc('ended_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'all' => LiveClass::query()->whereNotNull('recording_media_id')->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
            'released' => LiveClass::query()->whereNotNull('recording_media_id')->whereNotNull('recording_released_at')->where('recording_released_at', '<=', now())->whereIn('status', ['completed', 'published'])->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
            'scheduled' => LiveClass::query()->whereNotNull('recording_media_id')->where('recording_released_at', '>', now())->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))->count(),
        ];

        return view('owner.recordings.index', compact('academy', 'recordings', 'stats'));
    }

    public function updatePublication(Request $request, Academy $academy, int $liveClass): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'live_classes.manage');

        $data = $request->validate([
            'action' => ['required', 'string', 'in:publish,unpublish'],
            'release_at' => ['nullable', 'date', 'after:now'],
        ]);

        DB::transaction(function () use ($request, $academy, $liveClass, $data): void {
            $record = LiveClass::query()
                ->whereKey($liveClass)
                ->whereHas('course', fn ($course) => $course->where('academy_id', $academy->id))
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['action'] === 'unpublish') {
                $record->forceFill([
                    'recording_released_at' => null,
                    'recording_visibility' => 'private',
                    'status' => $record->status === 'published' ? 'completed' : $record->status,
                ])->save();

                return;
            }

            $media = $record->recording;
            $exists = $media
                && $media->status === 'active'
                && filled($media->path)
                && Storage::disk($media->disk)->exists($media->path);

            if (! $exists) {
                throw ValidationException::withMessages([
                    'action' => 'فایل ضبط معتبر در فضای ذخیره‌سازی پیدا نشد؛ انتشار انجام نشد.',
                ]);
            }

            $record->forceFill([
                'recording_released_at' => $data['release_at'] ?? now(),
                'recording_visibility' => 'enrolled_students',
                'status' => 'published',
            ])->save();
        });

        return redirect()->route('owner.recordings.index', $academy)
            ->with('success', $data['action'] === 'publish' ? 'زمان انتشار ضبط ذخیره شد.' : 'دسترسی دانش‌آموزان به این ضبط متوقف شد.');
    }

    private function authorizeAcademy(Request $request, Academy $academy, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('academy-owner')
            && $request->user()->hasPermission($permission)
            && (int) $academy->owner_id === (int) $request->user()->id,
            404
        );
    }
}
