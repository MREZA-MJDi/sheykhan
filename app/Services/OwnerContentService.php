<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\User;
use App\Models\AcademyContentCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $academy = $this->ownedAcademy($owner, (int) $data['academy_id']);
        $category = $academy->academyContentCategories()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($owner, $academy, $category, $data): AcademyContent {
            $content = $academy->academyContents()->create([
                'category_id' => $category->id,
                'type' => $data['type'],
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($academy, $data['title'], $data['slug'] ?? null),
                'excerpt' => $data['excerpt'] ?? null,
                'body' => $data['body'] ?? null,
                'video_duration_seconds' => $data['video_duration_seconds'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'published_at' => ($data['status'] ?? 'draft') === 'published'
                    ? ($data['published_at'] ?? now())
                    : null,
                'created_by' => $owner->id,
            ]);

            app(AcademyContentService::class)->clearPublicCache();

            return $content;
        });
    }

    public function update(User $owner, AcademyContent $content, array $data): AcademyContent
    {
        $academy = $this->ownedAcademy($owner, (int) $content->academy_id);
        $category = $academy->academyContentCategories()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        return DB::transaction(function () use ($academy, $content, $category, $data): AcademyContent {
            $status = $data['status'] ?? $content->status;

            $content->update([
                'category_id' => $category->id,
                'type' => $data['type'] ?? $content->type,
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

            app(AcademyContentService::class)->clearPublicCache();

            return $content->refresh();
        });
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
