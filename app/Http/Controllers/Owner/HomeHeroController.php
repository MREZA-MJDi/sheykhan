<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Website\HomeHeroRequest;
use App\Models\HomeHeroSetting;
use App\Models\HomeHeroSlide;
use App\Models\Media;
use App\Services\HomeHeroService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

final class HomeHeroController extends Controller
{
    private const MAX_SLIDES = 5;

    public function edit(): View {
        $this->ensureSeoSpecialist(request()->user());

        return view('owner.website.hero', [
            'slides' => HomeHeroSlide::query()
                ->with(['media', 'mobileMedia'])
                ->orderBy('slot')
                ->get()
                ->keyBy('slot'),
            'maxSlides' => self::MAX_SLIDES,
        ]);
    }

    public function update(
        HomeHeroRequest $request,
        MediaService $mediaService,
        HomeHeroService $heroService,
    ): RedirectResponse {
        $owner = $request->user();
        $this->ensureSeoSpecialist($owner);

        $data = $request->validated();
        $slot = (int) $data['slot'];
        $uploadedFiles = [];

        try {
            [$slide, $oldMediaIds] = DB::transaction(function () use (
                $request,
                $owner,
                $data,
                $slot,
                $mediaService,
                &$uploadedFiles
            ): array {
                $slide = HomeHeroSlide::query()
                    ->where('slot', $slot)
                    ->lockForUpdate()
                    ->first();

                $oldMediaIds = array_values(array_unique(array_filter([
                    $slide?->media_id,
                    $slide?->mobile_media_id,
                ])));

                if ($request->boolean('remove_slide')) {
                    $slide?->delete();

                    return [null, $oldMediaIds];
                }

                if (! $slide) {
                    $slide = new HomeHeroSlide();
                    $slide->slot = $slot;
                    $slide->crop_x = 50;
                    $slide->crop_y = 46;
                    $slide->mobile_crop_x = 54;
                    $slide->mobile_crop_y = 50;
                }

                if ($request->hasFile('image')) {
                    $media = $mediaService->upload($request->file('image'), null, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'website/home-hero/slides',
                        'collection' => 'home-hero',
                        'visibility' => 'public',
                        'uploaded_by' => $owner->id,
                    ]);

                    $uploadedFiles[] = [
                        'disk' => $media->disk,
                        'path' => $media->path,
                        'id' => $media->id,
                    ];
                    $slide->media_id = $media->id;
                }

                if ($request->hasFile('mobile_image')) {
                    $media = $mediaService->upload($request->file('mobile_image'), null, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'website/home-hero/slides/mobile',
                        'collection' => 'home-hero',
                        'visibility' => 'public',
                        'uploaded_by' => $owner->id,
                    ]);

                    $uploadedFiles[] = [
                        'disk' => $media->disk,
                        'path' => $media->path,
                        'id' => $media->id,
                    ];
                    $slide->mobile_media_id = $media->id;
                } elseif ($request->boolean('remove_mobile_image')) {
                    $slide->mobile_media_id = null;
                }

                $slide->title = filled($data['title'] ?? null) ? trim($data['title']) : null;
                $slide->description = filled($data['description'] ?? null) ? trim($data['description']) : null;
                $slide->cta_label = filled($data['cta_label'] ?? null) ? trim($data['cta_label']) : null;
                $slide->cta_url = filled($data['cta_url'] ?? null) ? trim($data['cta_url']) : null;
                $slide->crop_x = (int) ($data['crop_x'] ?? $slide->crop_x ?? 50);
                $slide->crop_y = (int) ($data['crop_y'] ?? $slide->crop_y ?? 46);
                $slide->mobile_crop_x = (int) ($data['mobile_crop_x'] ?? $slide->mobile_crop_x ?? 54);
                $slide->mobile_crop_y = (int) ($data['mobile_crop_y'] ?? $slide->mobile_crop_y ?? 50);
                $slide->is_active = (bool) ($data['is_active'] ?? false) && (bool) $slide->media_id;
                $slide->sort_order = $slot;
                $slide->updated_by = $owner->id;
                $slide->save();

                return [$slide, $oldMediaIds];
            });
        } catch (Throwable $exception) {
            foreach ($uploadedFiles as $uploadedFile) {
                try {
                    Storage::disk($uploadedFile['disk'])->delete($uploadedFile['path']);
                    Media::query()->whereKey($uploadedFile['id'])->delete();
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        $currentMediaIds = array_values(array_filter([
            $slide?->media_id,
            $slide?->mobile_media_id,
        ]));

        $this->deleteReplacedHeroMedia(
            array_diff($oldMediaIds, $currentMediaIds),
            $mediaService,
        );

        $heroService->forgetCache();

        return redirect()
            ->route('owner.website.hero.edit')
            ->with('success', $request->boolean('remove_slide')
                ? "اسلاید {$slot} حذف شد."
                : "تنظیمات اسلاید {$slot} ذخیره شد.");
    }

    private function ensureSeoSpecialist($user): void
    {
        abort_unless(
            $user
                && $user->hasRole('seo-specialist')
                && ! $user->hasRole('academy-owner')
                && $user->hasPermission('seo.manage'),
            403
        );
    }

    private function deleteReplacedHeroMedia(array $mediaIds, MediaService $mediaService): void
    {
        foreach (Media::query()->whereIn('id', $mediaIds)->where('collection', 'home-hero')->get() as $media) {
            $stillUsedBySlide = HomeHeroSlide::query()
                ->where('media_id', $media->id)
                ->orWhere('mobile_media_id', $media->id)
                ->exists();

            $stillUsedByLegacySettings = HomeHeroSetting::query()
                ->where('media_id', $media->id)
                ->orWhere('mobile_media_id', $media->id)
                ->exists();

            if ($media->attachments()->exists() || $stillUsedBySlide || $stillUsedByLegacySettings) {
                continue;
            }

            try {
                $mediaService->delete($media);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
