<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MediaService;

final class OwnerBlogService
{
    public function index(User $owner): array
    {
        $posts = BlogPost::query()
            ->where('author_id', $owner->id)
            ->with('category:id,name')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return [
            'posts' => $posts,
            'categories' => BlogCategory::query()->orderBy('name')->get(['id','name']),
            'stats' => [
                'total' => $posts->total(),
                'published' => BlogPost::query()->where('author_id',$owner->id)->where('status','published')->count(),
                'drafts' => BlogPost::query()->where('author_id',$owner->id)->where('status','draft')->count(),
            ],
        ];
    }

    public function formData(): array
    {
        return ['categories' => BlogCategory::query()->orderBy('name')->get(['id','name'])];
    }

    public function owned(User $owner, BlogPost $post): BlogPost
    {
        abort_unless((int) $post->author_id === (int) $owner->id, 404);

        return $post;
    }

    public function create(User $owner, array $data): BlogPost
    {
        $cover = $data['cover_image'] ?? null;
        unset($data['cover_image']);

        return DB::transaction(function () use ($owner, $data, $cover): BlogPost {
            $post = BlogPost::create([
                'category_id' => $data['category_id'] ?? null,
                'author_id' => $owner->id,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title'], $data['slug'] ?? null),
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
                'published_at' => ($data['status'] ?? 'draft') === 'published'
                    ? ($data['published_at'] ?? now())
                    : null,
            ]);

            if ($cover) {
                app(MediaService::class)->upload($cover, $post, [
                    'disk' => config('filesystems.default', 'local'),
                    'directory' => 'blog/covers/' . $post->id,
                    'collection' => 'cover',
                    'visibility' => 'public',
                    'sort_order' => 0,
                    'is_featured' => true,
                ]);
            }

            app(BlogService::class)->clearPublicCache();

            return $post->load('media');
        });
    }

    public function update(User $owner, BlogPost $post, array $data): BlogPost
    {
        $this->owned($owner, $post);

        $cover = $data['cover_image'] ?? null;
        unset($data['cover_image']);

        $status = $data['status'] ?? $post->status;

        DB::transaction(function () use ($post, $data, $status, $cover): void {
            $post->update([
                'category_id' => $data['category_id'] ?? null,
                'title' => $data['title'] ?? $post->title,
                'slug' => blank($data['slug'] ?? null)
                    ? $post->slug
                    : $this->uniqueSlug($data['title'] ?? $post->title, $data['slug'], $post->id),
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'] ?? $post->content,
                'status' => $status,
                'published_at' => $status === 'published'
                    ? ($data['published_at'] ?? $post->published_at ?? now())
                    : null,
            ]);

            if ($cover) {
                $media = $post->media()->wherePivot('collection', 'cover')->first();

                if ($media) {
                    app(MediaService::class)->replace($media, $cover, $post, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'blog/covers/' . $post->id,
                        'collection' => 'cover',
                        'visibility' => 'public',
                        'sort_order' => 0,
                        'is_featured' => true,
                    ]);
                } else {
                    app(MediaService::class)->upload($cover, $post, [
                        'disk' => config('filesystems.default', 'local'),
                        'directory' => 'blog/covers/' . $post->id,
                        'collection' => 'cover',
                        'visibility' => 'public',
                        'sort_order' => 0,
                        'is_featured' => true,
                    ]);
                }
            }
        });

        app(BlogService::class)->clearPublicCache();

        return $post->fresh('media');
    }

    private function uniqueSlug(string $title, ?string $requested = null, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::transliterate($requested ?: $title));
        if ($base === '') $base = 'article-' . Str::lower(Str::random(8));
        $slug = $base; $suffix = 2;
        while (BlogPost::query()->when($ignoreId !== null, fn ($q) => $q->where('blog_posts.id','!=',$ignoreId))->where('slug',$slug)->exists()) {
            $slug = $base . '-' . $suffix++;
        }
        return $slug;
    }
}
