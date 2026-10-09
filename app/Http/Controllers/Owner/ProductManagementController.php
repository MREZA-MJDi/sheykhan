<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class ProductManagementController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        return view('owner.products.index', [
            'academy' => $academy,
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'products' => Product::query()
                ->where('academy_id', $academy->id)
                ->with(['category:id,name', 'media' => fn ($query) => $query->wherePivot('collection', 'cover'), 'files.media'])
                ->orderByDesc('updated_at')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function store(
        Request $request,
        Academy $academy,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:12000'],
            'slug' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['book', 'booklet', 'exam', 'digital', 'file'])],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'cover_image' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'pdf_file' => ['nullable', 'file', 'max:51200', 'mimes:pdf'],
            'version' => ['nullable', 'string', 'max:32'],
            'publish' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $category = ProductCategory::query()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $publish = (bool) ($data['publish'] ?? false);
        if ($publish && empty($data['pdf_file'])) {
            throw ValidationException::withMessages([
                'pdf_file' => 'برای انتشار و فروش محصول دیجیتال، فایل PDF واقعی باید ثبت شود.',
            ]);
        }

        $slugBase = Str::slug(Str::transliterate($data['slug'] ?: $data['title']));
        if ($slugBase === '') {
            $slugBase = 'product-' . Str::lower(Str::random(10));
        }
        $slug = $slugBase;
        $suffix = 2;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $suffix++;
        }

        $uploaded = [];

        try {
            $product = DB::transaction(function () use ($request, $academy, $category, $data, $publish, $slug, $media, &$uploaded): Product {
                $product = Product::query()->create([
                    'academy_id' => $academy->id,
                    'category_id' => $category->id,
                    'created_by' => $request->user()->id,
                    'title' => trim($data['title']),
                    'slug' => $slug,
                    'subtitle' => $data['subtitle'] ?? null,
                    'description' => $data['description'] ?? null,
                    'product_type' => $data['product_type'],
                    'delivery_type' => 'download',
                    'price' => (int) $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'currency' => 'IRR',
                    'status' => $publish ? 'published' : 'draft',
                    'is_featured' => $publish && (bool) ($data['is_featured'] ?? false),
                    'published_at' => $publish ? now() : null,
                ]);

                if (! empty($data['cover_image'])) {
                    $uploaded[] = $media->upload($data['cover_image'], $product, [
                        'disk' => 'local',
                        'directory' => 'academies/' . $academy->id . '/products/covers',
                        'collection' => 'cover',
                        'visibility' => $publish ? 'public' : 'private',
                        'sort_order' => 0,
                        'is_featured' => true,
                    ]);
                }

                if (! empty($data['pdf_file'])) {
                    $pdf = $media->upload($data['pdf_file'], null, [
                        'disk' => 'local',
                        'directory' => 'academies/' . $academy->id . '/products/files',
                        'collection' => 'product-source',
                        'visibility' => 'private',
                    ]);
                    $uploaded[] = $pdf;

                    ProductFile::query()->create([
                        'product_id' => $product->id,
                        'media_id' => $pdf->id,
                        'version' => trim((string) ($data['version'] ?? '1.0')) ?: '1.0',
                        'is_primary' => true,
                        'is_preview' => false,
                        'requires_watermark' => true,
                    ]);
                }

                return $product;
            });
        } catch (Throwable $exception) {
            foreach ($uploaded as $file) {
                try {
                    \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->path);
                    $file->delete();
                } catch (Throwable) {
                    report(new \RuntimeException('A rolled-back product upload needs storage cleanup.'));
                }
            }
            throw $exception;
        }

        $this->clearCatalogCache();

        return back()->with('success', $publish
            ? 'محصول منتشر شد. PDF اصلی در فضای خصوصی ذخیره شده و تحویل خرید فقط به‌شکل واترمارک‌شده انجام می‌شود.'
            : 'پیش‌نویس محصول ذخیره شد. برای انتشار، ابتدا PDF واقعی اضافه کن.');
    }

    public function publish(
        Request $request,
        Academy $academy,
        Product $product,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $product->academy_id === (int) $academy->id, 404);

        $hasRealPdf = $product->files()
            ->where('is_preview', false)
            ->whereHas('media', fn ($query) => $query
                ->where('status', 'active')
                ->where('visibility', 'private')
                ->where('mime_type', 'application/pdf')
                ->whereNotNull('path'))
            ->exists();

        abort_unless($hasRealPdf, 422, 'بدون فایل PDF واقعی و خصوصی، انتشار محصول ممکن نیست.');

        DB::transaction(function () use ($product): void {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $locked->forceFill([
                'status' => 'published',
                'published_at' => now(),
            ])->save();

            foreach ($locked->media()->wherePivot('collection', 'cover')->get() as $cover) {
                $cover->forceFill(['visibility' => 'public'])->save();
            }
        });

        $this->clearCatalogCache();

        return back()->with('success', 'محصول منتشر شد.');
    }

    public function archive(
        Request $request,
        Academy $academy,
        Product $product,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $product->academy_id === (int) $academy->id, 404);

        DB::transaction(function () use ($product): void {
            $product->forceFill(['status' => 'draft', 'is_featured' => false])->save();
            foreach ($product->media()->wherePivot('collection', 'cover')->get() as $cover) {
                $cover->forceFill(['visibility' => 'private'])->save();
            }
        });

        $this->clearCatalogCache();

        return back()->with('success', 'محصول از فروش عمومی خارج شد؛ سوابق سفارش‌های قبلی حفظ می‌شوند.');
    }

    private function clearCatalogCache(): void
    {
        foreach ([1, 2, 3, 4, 6, 8, 12] as $limit) {
            Cache::forget('public:home:products:' . $limit);
        }
        Cache::forget('public:home:product-categories');
        Cache::forget('public:home:data:v3');
    }
}
