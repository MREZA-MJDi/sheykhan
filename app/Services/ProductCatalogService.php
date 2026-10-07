<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Cache;

class ProductCatalogService
{
    public function featuredCards(int $limit = 3): array
    {
        return Cache::remember("public:home:products:{$limit}", now()->addMinutes(5), function () use ($limit) {
            return Product::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with([
                    'category:id,name,slug',
                    'media' => fn ($query) => $query
                        ->where('visibility', 'public')
                        ->orderByPivot('sort_order'),
                ])
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Product $product) => [
                    'title' => $product->title,
                    'category' => $product->category?->name,
                    'category_slug' => $product->category?->slug,
                    'price' => $this->formatPrice($product->sale_price ?? $product->price),
                    'image' => $product->media->first()?->url(),
                ])
                ->all();
        });
    }

    public function categories(): array
    {
        return Cache::remember('public:home:product-categories', now()->addMinutes(10), fn () =>
            ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description'])
                ->map(fn (ProductCategory $category) => [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ])
                ->all()
        );
    }

    private function formatPrice(int|float|string $price): string
    {
        return number_format((float) $price, 0, '.', ',') . ' تومان';
    }
}
