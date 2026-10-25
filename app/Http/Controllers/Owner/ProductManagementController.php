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
use Illuminate\Support\Facades\Storage;
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


    public function edit(
        Request $request,
        Academy $academy,
        Product $product,
        OwnerWorkspaceService $workspace
    ): View {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $product->academy_id === (int) $academy->id, 404);

        $product->load(['category', 'files.media']);
        $cover = $product->media()->wherePivot('collection', 'cover')->get()->first();

        return view('owner.products.edit', [
            'academy' => $academy,
            'product' => $product,
            'cover' => $cover,
            'primaryFile' => $product->files()->where('is_primary', true)->with('media')->first(),
            'categories' => ProductCategory::query()
                ->where('is_active', true)
                ->orWhere('id', $product->category_id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'is_active']),
        ]);
    }

    public function update(
        Request $request,
        Academy $academy,
        Product $product,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless((int) $product->academy_id === (int) $academy->id, 404);

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:12000'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'product_type' => ['required', Rule::in(['book', 'booklet', 'exam', 'digital', 'file'])],
            'price' => ['required', 'integer', 'min:0', 'max:1000000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lte:price'],
            'cover_image' => ['nullable', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp'],
            'pdf_file' => ['nullable', 'file', 'max:51200', 'mimes:pdf'],
            'version' => ['nullable', 'string', 'max:32'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $category = ProductCategory::query()
            ->whereKey((int) $data['category_id'])
            ->where(function ($query) use ($product): void {
                $query->where('is_active', true)->orWhere('id', $product->category_id);
            })
            ->firstOrFail();

        $rawSlug = trim((string) ($data['slug'] ?? ''));
        $slugBase = Str::slug(Str::transliterate($rawSlug !== '' ? $rawSlug : (string) $data['title']));
        if ($slugBase === '') {
            throw ValidationException::withMessages([
                'slug' => 'از عنوان محصول یک نامک معتبر بساز یا نامک را وارد کن.',
            ]);
        }
        $slug = $slugBase;
        if ($rawSlug === '') {
            $suffix = 2;
            while (Product::query()->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $slugBase . '-' . $suffix++;
            }
        }

        $existingPrimary = $product->files()->where('is_primary', true)->with('media')->first();
        if ($product->status === 'published' && empty($data['pdf_file'])) {
            $currentMedia = $existingPrimary?->media;
            $currentPdfAvailable = $currentMedia
                && $currentMedia->status === 'active'
                && $currentMedia->visibility === 'private'
                && strtolower((string) $currentMedia->mime_type) === 'application/pdf'
                && filled($currentMedia->path)
                && Storage::disk($currentMedia->disk)->exists($currentMedia->path);

            if (! $currentPdfAvailable) {
                throw ValidationException::withMessages([
                    'pdf_file' => 'فایل PDF اصلی محصول منتشرشده در دسترس نیست؛ برای ادامه یک PDF معتبر بارگذاری کن.',
                ]);
            }
        }

        $uploaded = [];
        $newCover = null;
        $newPdf = null;
        $oldCovers = $product->media()->wherePivot('collection', 'cover')->get();

        try {
            if (! empty($data['cover_image'])) {
                $newCover = $media->upload($data['cover_image'], null, [
                    'disk' => 'local',
                    'directory' => 'academies/' . $academy->id . '/products/covers',
                    'collection' => 'cover',
                    'visibility' => $product->status === 'published' ? 'public' : 'private',
                ]);
                $uploaded[] = $newCover;
            }

            if (! empty($data['pdf_file'])) {
                $newPdf = $media->upload($data['pdf_file'], null, [
                    'disk' => 'local',
                    'directory' => 'academies/' . $academy->id . '/products/files',
                    'collection' => 'product-source',
                    'visibility' => 'private',
                ]);
                $uploaded[] = $newPdf;
            }

            DB::transaction(function () use ($academy, $product, $data, $category, $slug, $newCover, $newPdf, $media): void {
                $locked = Product::query()
                    ->where('academy_id', $academy->id)
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked->fill([
                    'category_id' => $category->id,
                    'title' => trim($data['title']),
                    'slug' => $slug,
                    'subtitle' => filled($data['subtitle'] ?? null) ? trim((string) $data['subtitle']) : null,
                    'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
                    'product_type' => $data['product_type'],
                    'price' => (int) $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'is_featured' => $locked->status === 'published' && (bool) ($data['is_featured'] ?? false),
                ])->save();

                if ($newCover) {
                    foreach ($locked->media()->wherePivot('collection', 'cover')->get() as $oldCover) {
                        $media->detach($oldCover, $locked);
                    }
                    $media->attach($newCover, $locked, 'cover', 0, $locked->status === 'published');
                }

                if ($newPdf) {
                    // Keep old files as version history; only the latest replacement is primary.
                    $locked->files()->where('is_primary', true)->update(['is_primary' => false]);
                    $locked->files()->create([
                        'media_id' => $newPdf->id,
                        'version' => trim((string) ($data['version'] ?? '1.0')) ?: '1.0',
                        'is_primary' => true,
                        'is_preview' => false,
                        'requires_watermark' => true,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($uploaded as $file) {
                try {
                    if ($file->attachments()->exists()) {
                        $file->attachments()->delete();
                    }
                    if (! ProductFile::query()->where('media_id', $file->id)->exists()) {
                        $media->delete($file);
                    }
                } catch (Throwable $cleanupException) {
                    report(new \RuntimeException('A rolled-back product update upload needs storage cleanup.', 0, $cleanupException));
                }
            }
            throw $exception;
        }

        if ($newCover) {
            foreach ($oldCovers as $oldCover) {
                try {
                    if ($oldCover->attachments()->doesntExist()
                        && ! ProductFile::query()->where('media_id', $oldCover->id)->exists()) {
                        $media->delete($oldCover);
                    }
                } catch (Throwable $cleanupException) {
                    report(new \RuntimeException('A replaced product cover could not be cleaned up.', 0, $cleanupException));
                }
            }
        }

        $this->clearCatalogCache();

        return redirect()->route('owner.products.edit', [$academy, $product])
            ->with('success', 'اطلاعات محصول ذخیره شد؛ نسخه‌های قبلی فایل حفظ شدند و فایل جدید به‌صورت خصوصی و واترمارک‌پذیر ثبت شد.');
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
            ->with('media')
            ->get()
            ->contains(function ($file): bool {
                $media = $file->media;

                return $media
                    && $media->status === 'active'
                    && $media->visibility === 'private'
                    && strtolower((string) $media->mime_type) === 'application/pdf'
                    && filled($media->path)
                    && Storage::disk($media->disk)->exists($media->path);
            });

        abort_unless($hasRealPdf, 422, 'بدون فایل PDF واقعی موجود در فضای خصوصی، انتشار محصول ممکن نیست.');

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
