<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Academy;
use App\Models\Testimonial;
use App\Services\AchievementService;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use App\Services\TestimonialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class ShowcaseController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        return view('owner.showcase.index', [
            'academy' => $academy,
            'achievements' => Achievement::query()
                ->where('academy_id', $academy->id)
                ->with(['grade:id,title', 'media:id,original_name,visibility,status'])
                ->orderByDesc('updated_at')
                ->limit(30)
                ->get(),
            'testimonials' => Testimonial::query()
                ->where('academy_id', $academy->id)
                ->with(['media:id,original_name,mime_type,visibility,status'])
                ->orderByDesc('updated_at')
                ->limit(30)
                ->get(),
            'grades' => \App\Models\AcademicGrade::query()->orderBy('sort_order')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeAchievement(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:160'],
            'achievement_type' => ['required', Rule::in(['gifted_school', 'sample_school'])],
            'school_name' => ['required', 'string', 'max:180'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'grade_id' => ['nullable', 'integer', 'exists:academic_grades,id'],
            'image' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'publication_consent_confirmed' => ['accepted'],
            'guardian_consent_confirmed' => ['accepted'],
            'publication_consent_method' => ['required', Rule::in(['written', 'email', 'message', 'paper'])],
            'publication_consent_reference' => ['required', 'string', 'max:160'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $publish = (bool) ($data['publish'] ?? false);
        $uploaded = null;

        try {
            if (! empty($data['image'])) {
                $uploaded = $media->upload($data['image'], null, [
                    'disk' => 'local',
                    'directory' => 'academies/' . $academy->id . '/achievements',
                    'collection' => 'achievements',
                    'visibility' => $publish ? 'public' : 'private',
                ]);
            }

            DB::transaction(function () use ($request, $academy, $data, $publish, $uploaded): void {
                Achievement::query()->create([
                    'academy_id' => $academy->id,
                    'student_id' => null,
                    'grade_id' => $data['grade_id'] ?? null,
                    'display_name' => $data['display_name'],
                    'achievement_type' => $data['achievement_type'],
                    'school_name' => $data['school_name'],
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'media_id' => $uploaded?->id,
                    'status' => $publish ? 'published' : 'draft',
                    'is_featured' => $publish,
                    'published_at' => $publish ? now() : null,
                    'created_by' => $request->user()->id,
                    'publication_consent_at' => now(),
                    'publication_consent_method' => $data['publication_consent_method'],
                    'publication_consent_reference' => trim($data['publication_consent_reference']),
                    'publication_consent_for_minor' => true,
                    'publication_consent_recorded_by' => $request->user()->id,
                ]);
            });
        } catch (Throwable $exception) {
            if ($uploaded) {
                $media->delete($uploaded);
            }
            throw $exception;
        }

        $this->clearPublicCache();

        return back()->with('success', $publish
            ? 'افتخارآفرین با ثبت سابقهٔ رضایت منتشر شد.'
            : 'افتخارآفرین به‌صورت پیش‌نویس ذخیره شد و هنوز عمومی نیست.');
    }

    public function storeTestimonial(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:160'],
            'role' => ['required', Rule::in(['parent', 'student'])],
            'content_text' => ['required', 'string', 'max:3000'],
            'image_file' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'audio_file' => ['nullable', 'file', 'max:51200', 'mimes:mp3,m4a,aac,ogg,wav'],
            'video_file' => ['nullable', 'file', 'max:204800', 'mimes:mp4,webm,mov'],
            'publication_consent_confirmed' => ['accepted'],
            'guardian_consent_confirmed' => ['accepted'],
            'publication_consent_method' => ['required', Rule::in(['written', 'email', 'message', 'paper'])],
            'publication_consent_reference' => ['required', 'string', 'max:160'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        if (! $data['image_file'] && ! $data['audio_file'] && ! $data['video_file'] && trim($data['content_text']) === '') {
            throw ValidationException::withMessages([
                'content_text' => 'متن یا حداقل یک رسانه برای ثبت تجربه لازم است.',
            ]);
        }

        $publish = (bool) ($data['publish'] ?? false);
        $uploaded = [];

        try {
            $testimonial = DB::transaction(function () use ($request, $academy, $data, $publish, $media, &$uploaded): Testimonial {
                $testimonial = Testimonial::query()->create([
                    'academy_id' => $academy->id,
                    'user_id' => null,
                    'display_name' => $data['display_name'],
                    'role' => $data['role'],
                    'content_text' => trim($data['content_text']),
                    'status' => $publish ? 'approved' : 'pending',
                    'is_featured' => $publish,
                    'sort_order' => (int) Testimonial::query()->where('academy_id', $academy->id)->max('sort_order') + 1,
                    'published_at' => $publish ? now() : null,
                    'publication_consent_at' => now(),
                    'publication_consent_method' => $data['publication_consent_method'],
                    'publication_consent_reference' => trim($data['publication_consent_reference']),
                    'publication_consent_for_minor' => true,
                    'publication_consent_recorded_by' => $request->user()->id,
                ]);

                $visibility = $publish ? 'public' : 'private';
                foreach ([
                    'image_file' => ['collection' => 'image', 'directory' => 'images'],
                    'audio_file' => ['collection' => 'audio', 'directory' => 'audio'],
                    'video_file' => ['collection' => 'video', 'directory' => 'video'],
                ] as $field => $options) {
                    if (empty($data[$field])) {
                        continue;
                    }

                    $uploaded[] = $media->upload($data[$field], $testimonial, [
                        'disk' => 'local',
                        'directory' => 'academies/' . $academy->id . '/testimonials/' . $options['directory'],
                        'collection' => $options['collection'],
                        'visibility' => $visibility,
                        'sort_order' => count($uploaded),
                    ]);
                }

                return $testimonial;
            });
        } catch (Throwable $exception) {
            foreach ($uploaded as $item) {
                try {
                    $media->delete($item);
                } catch (Throwable) {
                    report(new \RuntimeException('A rolled-back testimonial upload needs storage cleanup.'));
                }
            }
            throw $exception;
        }

        $this->clearPublicCache();

        return back()->with('success', $publish
            ? 'تجربه پس از ثبت سابقهٔ رضایت منتشر شد.'
            : 'تجربه ذخیره شد، اما تا انتشار از پنل برای عموم نمایش داده نمی‌شود.');
    }

    public function withdrawAchievement(
        Academy $academy,
        Achievement $achievement,
        Request $request,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $achievement->academy_id === (int) $academy->id, 404);

        $achievement->update(['status' => 'draft', 'published_at' => null, 'is_featured' => false]);
        if ($achievement->media) {
            $achievement->media->forceFill(['visibility' => 'private'])->save();
        }

        $this->clearPublicCache();

        return back()->with('success', 'افتخارآفرین از نمایش عمومی خارج شد.');
    }

    public function withdrawTestimonial(
        Academy $academy,
        Testimonial $testimonial,
        Request $request,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $testimonial->academy_id === (int) $academy->id, 404);

        DB::transaction(function () use ($testimonial): void {
            $testimonial->update(['status' => 'pending', 'published_at' => null, 'is_featured' => false]);
            foreach ($testimonial->media()->get() as $item) {
                $item->forceFill(['visibility' => 'private'])->save();
            }
        });

        $this->clearPublicCache();

        return back()->with('success', 'تجربه از نمایش عمومی خارج شد.');
    }

    private function clearPublicCache(): void
    {
        Cache::forget('public:home:data:v3');
        Cache::forget('public:home:achievements:6');
        Cache::forget('public:home:testimonials:6');
        app(AchievementService::class);
        app(TestimonialService::class);
    }
}
