<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Website\HomeHeroRequest;
use App\Models\Academy;
use App\Models\HomeHeroSetting;
use App\Models\Media;
use App\Services\HomeBannerService;
use App\Services\HomeHeroService;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

final class HomeHeroController extends Controller
{
    public function edit(
        OwnerWorkspaceService $workspace,
        HomeHeroService $heroService,
    ): View {
        $this->ensureOwnerHasWorkspace(request()->user(), $workspace);

        $hero = HomeHeroSetting::query()
            ->with(['media', 'mobileMedia'])
            ->find(1);

        return view('owner.website.hero', [
            'hero' => $hero,
            'heroConfig' => $heroService->publicConfig(),
        ]);
    }

    public function update(
        HomeHeroRequest $request,
        OwnerWorkspaceService $workspace,
        MediaService $mediaService,
        HomeBannerService $bannerService,
    ): RedirectResponse {
        $owner = $request->user();
        $this->ensureOwnerHasWorkspace($owner, $workspace);
        $uploadedFiles = [];

        try {
            [$hero, $oldMediaIds] = DB::transaction(function () use (
                $request,
                $owner,
                $mediaService,
                &$uploadedFiles
            ): array {
                $hero = HomeHeroSetting::query()->lockForUpdate()->find(1);

                if (! $hero) {
                    $hero = new HomeHeroSetting();
                    $hero->id = 1;
                    $hero->crop_x = 50;
                    $hero->crop_y = 46;
                    $hero->mobile_crop_x = 54;
                    $hero->mobile_crop_y = 50;
                }

                $oldMediaIds = array_values(array_filter([
                    $hero->media_id,
                    $hero->mobile_media_id,
                ]));

                if ($request->boolean('restore_default')) {
                    $hero->media_id = null;
                    $hero->mobile_media_id = null;
                    $hero->crop_x = 50;
                    $hero->crop_y = 46;
                    $hero->mobile_crop_x = 54;
                    $hero->mobile_crop_y = 50;
                } else {
                    if ($request->hasFile('image')) {
                        $media = $mediaService->upload($request->file('image'), null, [
                            'disk' => config('filesystems.default', 'local'),
                            'directory' => 'website/home-hero',
                            'collection' => 'home-hero',
                            'visibility' => 'public',
                            'uploaded_by' => $owner->id,
                        ]);

                        $uploadedFiles[] = ['disk' => $media->disk, 'path' => $media->path, 'id' => $media->id];
                        $hero->media_id = $media->id;
                    }

                    if ($request->hasFile('mobile_image')) {
                        $media = $mediaService->upload($request->file('mobile_image'), null, [
                            'disk' => config('filesystems.default', 'local'),
                            'directory' => 'website/home-hero/mobile',
                            'collection' => 'home-hero',
                            'visibility' => 'public',
                            'uploaded_by' => $owner->id,
                        ]);

                        $uploadedFiles[] = ['disk' => $media->disk, 'path' => $media->path, 'id' => $media->id];
                        $hero->mobile_media_id = $media->id;
                    } elseif ($request->boolean('remove_mobile_image')) {
                        $hero->mobile_media_id = null;
                    }

                    $hero->crop_x = (int) $request->validated('crop_x');
                    $hero->crop_y = (int) $request->validated('crop_y');
                    $hero->mobile_crop_x = (int) $request->validated('mobile_crop_x');
                    $hero->mobile_crop_y = (int) $request->validated('mobile_crop_y');
                }

                $hero->updated_by = $owner->id;
                $hero->save();

                return [$hero, $oldMediaIds];
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
            $hero->media_id,
            $hero->mobile_media_id,
        ]));

        $this->deleteReplacedHeroMedia(
            array_diff($oldMediaIds, $currentMediaIds),
            $mediaService,
        );

        $bannerService->forgetCache();

        return redirect()
            ->route('owner.website.hero.edit')
            ->with('success', 'تصویر هیروی صفحه اصلی با موفقیت ذخیره شد.');
    }

    private function ensureOwnerHasWorkspace($owner, OwnerWorkspaceService $workspace): void
    {
        // The public homepage is a platform-wide surface. Only the owner of the
        // canonical platform academy may change it; academy owners still manage
        // their own three lower-page banner slots through the existing screen.
        $canManagePublicSite = $workspace->academies($owner)
            ->contains(fn (Academy $academy): bool => $academy->slug === 'sheykhan-academy');

        abort_unless($canManagePublicSite, 403);
    }

    private function deleteReplacedHeroMedia(array $mediaIds, MediaService $mediaService): void
    {
        foreach (Media::query()->whereIn('id', $mediaIds)->where('collection', 'home-hero')->get() as $media) {
            if ($media->attachments()->exists()) {
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
