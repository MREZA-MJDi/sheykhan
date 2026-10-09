<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\User;
use App\Models\AcademyContentCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class OwnerContentService
{
    public function index(User $owner): array
    {
        $academies = $this->ownedAcademies($owner);

        $contents = AcademyContent::query()
            ->whereIn('academy_id', $academies->modelKeys())
            ->with('academy:id,name', 'category:id,title,academy_id')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (int) $contents->total(),
            'published' => AcademyContent::query()->whereIn('academy_id', $academies->modelKeys())->where('status', 'published')->count(),
            'drafts' => AcademyContent::query()->whereIn('academy_id', $academies->modelKeys())->where('status', 'draft')->count(),
        ];

        return compact('academies', 'contents', 'stats');
    }

    public function formData(User $owner, ?AcademyContent $content = null): array
    {
        $academies = $this->ownedAcademies($owner);
        $academyId = $content?->academy_id ?: $academies->first()?->id;

        return [
            'academies' => $academies,
            'categories' => $academyId
                ? AcademyContentCategory::query()
                    ->where('academy_id', $academyId)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'academy_id', 'title'])
                : collect(),
        ];
    }

    public function create(User $owner, array $data): AcademyContent
    {
        $cover = $data['cover_image'] ?? null;
        $videoFile = $data['video_file'] ?? null;
        unset($data['cover_image'], $data['video_file']);

        $status = $data['status'] ?? 'draft';
        if (($data['type'] ?? 'article') === 'video' && $status === 'published' && ! $videoFile) {
            throw ValidationException::withMessages([
                'video_file' => 'برای انتشار ویدئو، فایل واقعی ویدئو را بارگذاری کن.',
            ]);
        }

        $academy = $this->ownedAcademy($owner, (int) $data['academy_id']);
        $category = $academy->academyContentCategories()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $createdMedia = [];

        try {
            $content = DB::transaction(function () use ($owner, $academy, $category, $data, $cover, $videoFile, $status, &$createdMedia): AcademyContent {
                $content = $academy->academyContents()->create([
                    'category_id' => $category->id,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'slug' => $this->uniqueSlug($academy, $data['title'], $data['slug'] ?? null),
                    'excerpt' => $data['excerpt'] ?? null,
                    'body' => $data['body'] ?? null,
                    'video_duration_seconds' => $data['video_duration_seconds'] ?? null,
                    'status' => $status,
                    'is_featured' => (bool) ($data['is_featured'] ?? false),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'published_at' => $status === 'published' ? ($data['published_at'] ?? now()) : null,
                    'created_by' => $owner->id,
                ]);

                $visibility = $status === 'published' ? 'public' : 'private';
                $service = app(MediaService::class);

                if ($cover) {
                    $createdMedia[] = $service->upload($cover, $content, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'academy-content/' . $academy->id . '/covers',
                        'collection' => 'cover',
                        'visibility' => $visibility,
                        'sort_order' => 0,
                        'is_featured' => true,
                    ]);
                }

                if ($videoFile) {
                    $createdMedia[] = $service->upload($videoFile, $content, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'academy-content/' . $academy->id . '/videos',
                        'collection' => 'video',
                        'visibility' => $visibility,
                        'sort_order' => 1,
                        'is_featured' => true,
                    ]);
                }

                return $content->load('media');
            });
        } catch (Throwable $exception) {
            $this->cleanupRolledBackUploads($createdMedia);
            throw $exception;
        }

        app(AcademyContentService::class)->clearPublicCache();

        return $content;
    }

    public function update(User $owner, AcademyContent $content, array $data): AcademyContent
    {
        $cover = $data['cover_image'] ?? null;
        $videoFile = $data['video_file'] ?? null;
        unset($data['cover_image'], $data['video_file']);

        $academy = $this->ownedAcademy($owner, (int) $content->academy_id);
        $category = $academy->academyContentCategories()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $status = $data['status'] ?? $content->status;
        $type = $data['type'] ?? $content->type;
        $oldCover = $content->media()->wherePivot('collection', 'cover')->first();
        $oldVideo = $content->media()->wherePivot('collection', 'video')->first();

        if ($type === 'video' && $status === 'published' && ! $videoFile && ! $oldVideo) {
            throw ValidationException::withMessages([
                'video_file' => 'برای انتشار ویدئو باید فایل ویدئویی واقعی ثبت شده باشد.',
            ]);
        }

        $createdMedia = [];

        try {
            $updated = DB::transaction(function () use (
                $academy, $content, $category, $data, $cover, $videoFile, $status, $type,
                $oldCover, $oldVideo, &$createdMedia
            ): AcademyContent {
                $content->update([
                    'category_id' => $category->id,
                    'type' => $type,
                    'title' => $data['title'] ?? $content->title,
                    'slug' => blank($data['slug'] ?? null)
                        ? $content->slug
                        : $this->uniqueSlug($academy, $data['title'] ?? $content->title, $data['slug'], $content->id),
                    'excerpt' => $data['excerpt'] ?? null,
                    'body' => $data['body'] ?? null,
                    'video_duration_seconds' => $data['video_duration_seconds'] ?? null,
                    'status' => $status,
                    'is_featured' => (bool) ($data['is_featured'] ?? false),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'published_at' => $status === 'published'
                        ? ($data['published_at'] ?? $content->published_at ?? now())
                        : null,
                ]);

                $visibility = $status === 'published' ? 'public' : 'private';
                $service = app(MediaService::class);

                if ($cover) {
                    $createdMedia[] = $service->upload($cover, $content, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'academy-content/' . $academy->id . '/covers',
                        'collection' => 'cover',
                        'visibility' => $visibility,
                        'sort_order' => 0,
                        'is_featured' => true,
                    ]);
                    if ($oldCover) {
                        $content->media()->detach($oldCover->id);
                    }
                }

                if ($videoFile) {
                    $createdMedia[] = $service->upload($videoFile, $content, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'academy-content/' . $academy->id . '/videos',
                        'collection' => 'video',
                        'visibility' => $visibility,
                        'sort_order' => 1,
                        'is_featured' => true,
                    ]);
                    if ($oldVideo) {
                        $content->media()->detach($oldVideo->id);
                    }
                }

                foreach ($content->media()->get() as $attachedMedia) {
                    if (in_array($attachedMedia->pivot?->collection, ['cover', 'video'], true)
                        && $attachedMedia->visibility !== $visibility) {
                        $attachedMedia->forceFill(['visibility' => $visibility])->save();
                    }
                }

                return $content->fresh('media');
            });
        } catch (Throwable $exception) {
            $this->cleanupRolledBackUploads($createdMedia);
            throw $exception;
        }

        foreach ([$cover ? $oldCover : null, $videoFile ? $oldVideo : null] as $oldMedia) {
            if ($oldMedia && $oldMedia->attachments()->doesntExist()) {
                app(MediaService::class)->delete($oldMedia);
            }
        }

        app(AcademyContentService::class)->clearPublicCache();

        return $updated;
    }

    private function cleanupRolledBackUploads(array $uploaded): void
    {
        foreach ($uploaded as $media) {
            try {
                \Illuminate\Support\Facades\Storage::disk($media->disk)->delete($media->path);
                $media->delete();
            } catch (Throwable) {
                report(new \RuntimeException('A rolled-back academy content upload needs storage cleanup.'));
            }
        }
    }

    public function owned(User $owner, AcademyContent $content): AcademyContent
    {
        abort_unless(
            $owner->hasRole('academy-owner')
                && $content->academy?->owner_id === $owner->id,
            403
        );

        return $content;
    }

    private function ownedAcademies(User $owner)
    {
        return $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    private function ownedAcademy(User $owner, int $academyId): Academy
    {
        $academy = $owner->ownedAcademies()
            ->where('status', 'active')
            ->whereKey($academyId)
            ->first();

        if (!$academy) {
            throw ValidationException::withMessages([
                'academy_id' => 'این آموزشگاه برای شما قابل مدیریت نیست.',
            ]);
        }

        return $academy;
    }

    private function uniqueSlug(Academy $academy, string $title, ?string $requested = null, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::transliterate($requested ?: $title));

        if ($base === '') {
            $base = 'content-' . Str::lower(Str::random(8));
        }

        $slug = $base;
        $suffix = 2;

        while ($academy->academyContents()
            ->when($ignoreId !== null, fn ($query) => $query->where('academy_contents.id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
