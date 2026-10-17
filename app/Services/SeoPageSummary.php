<?php

namespace App\Services;

use Illuminate\Support\Collection;

final class SeoPageSummary
{
    public function statsFor(Collection $items): array
    {
        return [
            'total' => $items->count(),
            'configured' => $items->where('score', '>', 0)->count(),
            'ready' => $items->where('score', '>=', 80)->count(),
            'needsWork' => $items->where('score', '<', 80)->count(),
        ];
    }

    public function mapItem(
        object $model,
        string $type,
        string $label,
        ?object $seo,
        ?string $academy = null
    ): array {
        $fields = [
            'title' => $seo?->title,
            'description' => $seo?->description,
            'canonical_url' => $seo?->canonical_url,
            'og_title' => $seo?->og_title,
            'og_description' => $seo?->og_description,
            'og_image_url' => $seo?->og_image_url,
            'schema_json' => $seo?->schema_json,
        ];

        $filled = collect($fields)->filter(fn ($value) => filled($value))->count();
        $score = (int) round(($filled / count($fields)) * 100);
        $url = match ($type) {
            'course' => route('courses.show', $model),
            'content' => $model->academy ? route('academy.content.show', [$model->academy->slug, $model->slug]) : null,
            'blog' => route('blog.show', $model->slug),
            'product' => route('store.product.show', $model),
            'teacher' => route('teachers.show', $model),
            default => null,
        };

        return [
            'type' => $type,
            'id' => $model->getKey(),
            'label' => $label,
            'academy' => $academy,
            'url' => $url,
            'robots' => $seo?->robots ?: 'index,follow',
            'score' => $score,
            'status' => $score >= 80 ? 'ready' : ($score > 0 ? 'partial' : 'empty'),
        ];
    }
}
