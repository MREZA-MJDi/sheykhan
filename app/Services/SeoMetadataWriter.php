<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class SeoMetadataWriter
{
    public function update(Model $model, array $data): void
    {
        $payload = [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'keywords' => $data['keywords'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'robots' => $data['robots'] ?? 'index,follow',
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'og_image_url' => $data['og_image_url'] ?? null,
            'schema_json' => filled($data['schema_json'] ?? null)
                ? json_decode($data['schema_json'], true, 512, JSON_THROW_ON_ERROR)
                : null,
        ];

        $model->seoMeta()->updateOrCreate([], $payload);
        Cache::forget('public:seo:sitemap:v1');
    }
}
