<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Academy\HomeBannerRequest;
use App\Models\Academy;
use App\Models\HomeBanner;
use App\Models\Media;
use App\Services\HomeBannerService;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HomeBannerController extends Controller
{
    public function edit(
        Academy $academy,
        OwnerWorkspaceService $workspace
    ) {
        abort_unless($workspace->canManageAcademy(request()->user(), $academy), 403);

        $academy->load([
            'media' => fn ($query) => $query
                ->where('visibility', 'public')
                ->where('status', 'active')
                ->where('mime_type', 'like', 'image/%')
                ->orderBy('id', 'desc'),
            'homeBanners.media',
        ]);

        return view('owner.academy.banners', compact('academy'));
    }

    public function update(
        HomeBannerRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        MediaService $mediaService,
        HomeBannerService $bannerService,
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $payload = $request->validated()['banners'] ?? [];
        $academy->load('media');
        $attachedMediaIds = $academy->media->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use (
            $payload,
            $academy,
            $attachedMediaIds,
            $mediaService
        ): void {
            for ($slot = 1; $slot <= 3; $slot++) {
                $data = $payload[$slot] ?? $payload[(string) $slot] ?? [];
                $banner = HomeBanner::query()->firstOrNew([
                    'academy_id' => $academy->id,
                    'slot' => $slot,
                ]);

                if (!$banner->exists && empty($data)) {
                    continue;
                }

                if (!empty($data['image'])) {
                    $newMedia = $mediaService->upload(
                        $data['image'],
                        $academy,
                        [
                            'disk' => 'local',
                            'directory' => 'academies/' . $academy->id . '/home-banners',
                            'collection' => 'home-banners',
                            'visibility' => 'public',
                        ],
                    );

                    $banner->media_id = $newMedia->id;
                } elseif (!empty($data['media_id'])) {
                    $mediaId = (int) $data['media_id'];

                    if (!in_array($mediaId, $attachedMediaIds, true)) {
                        abort(422, 'بنر انتخاب‌شده متعلق به این آموزشگاه نیست.');
                    }

                    $media = Media::query()
                        ->whereKey($mediaId)
                        ->where('visibility', 'public')
                        ->where('status', 'active')
                        ->where('mime_type', 'like', 'image/%')
                        ->first();

                    abort_unless($media, 422);
                    $banner->media_id = $media->id;
                }

                if (!$banner->media_id) {
                    $banner->is_active = false;
                } else {
                    $banner->title = $data['title'] ?? null;
                    $banner->description = $data['description'] ?? null;
                    $banner->cta_label = $data['cta_label'] ?? null;
                    $banner->cta_url = $data['cta_url'] ?? null;
                    $banner->sort_order = $slot;
                    $banner->crop_x = (int) ($data['crop_x'] ?? 50);
                    $banner->crop_y = (int) ($data['crop_y'] ?? 50);
                    $banner->is_active = (bool) ($data['is_active'] ?? false);
                }

                $banner->save();
            }
        });

        $bannerService->forgetCache();

        return back()->with('success', 'بنرهای صفحه اصلی به‌روزرسانی شدند.');
    }
}
