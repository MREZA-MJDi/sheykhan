<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

final class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $product = Product::query()
            ->whereKey($product->getKey())
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category:id,name,slug',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->where('status', 'active')
                    ->orderByPivot('sort_order'),
                'seoMeta',
            ])
            ->firstOrFail();

        return view('pages.store.show', [
            'product' => $product,
            'seoMeta' => $product->seoMeta,
        ]);
    }
}
