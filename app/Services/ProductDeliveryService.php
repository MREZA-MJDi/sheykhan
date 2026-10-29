<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Order;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\ProtectedFile;
use App\Models\ProtectedFileView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Throwable;

final class ProductDeliveryService
{
    private const MAX_SOURCE_BYTES = 80_000_000;
    private const MAX_RENDERED_PAGES = 250;

    public function viewLearningMedia(
        User $user,
        Media $media,
        Request $request,
        ?\App\Models\LearningResource $resource = null,
        ?int $childId = null,
        ?string $backUrl = null,
    ): Response {
        $this->recordLearningMediaView($user, $media, $resource?->id, $childId, null, 'open', $request);

        try {
            $render = $this->prepareLearningMediaPages($user, $media);
        } catch (Throwable $exception) {
            report($exception);
            abort(503, 'نمایشگر امن نتوانست فایل آموزشی را آماده کند؛ فایل اصلی برای جلوگیری از دانلود ارائه نشد.');
        }

        $referenceCode = 'MEDIA-' . $media->id;

        $pageUrls = collect(range(1, $render['page_count']))->map(
            fn (int $page): string => route('protected-learning-media.pages', array_filter([
                'media' => $media->id,
                'page' => $page,
                'resource' => $resource?->id,
                'child' => $childId,
            ], fn ($value): bool => $value !== null))
        );

        return response()->view('commerce.protected-files.viewer', [
            'order' => null,
            'file' => null,
            'media' => $media,
            'product' => null,
            'pageCount' => $render['page_count'],
            'pageUrls' => $pageUrls,
            'textContent' => null,
            'user' => $user,
            'referenceCode' => $referenceCode,
            'backUrl' => $backUrl ?: ($user->hasRole('parent')
                ? route('parent.resources.index', $childId ? ['child' => $childId] : [])
                : route('student.resources.index')),
        ])->header('Cache-Control', 'no-store, private')
          ->header('Pragma', 'no-cache')
          ->header('Referrer-Policy', 'no-referrer')
          ->header('X-Content-Type-Options', 'nosniff');
    }

    public function viewLearningMediaPage(
        User $user,
        Media $media,
        int $page,
        Request $request,
        ?\App\Models\LearningResource $resource = null,
        ?int $childId = null,
    ): Response {
        try {
            $render = $this->prepareLearningMediaPages($user, $media);
        } catch (Throwable $exception) {
            report($exception);
            abort(503, 'صفحهٔ فایل محافظت‌شده آماده نشد؛ فایل اصلی ارائه نمی‌شود.');
        }

        abort_unless($page >= 1 && $page <= $render['page_count'], 404);

        $relativePath = $render['directory'] . '/page-' . str_pad((string) $page, 4, '0', STR_PAD_LEFT) . '.png';
        $disk = Storage::disk('local');
        abort_unless($disk->exists($relativePath), 404);

        $this->recordLearningMediaView($user, $media, $resource?->id, $childId, $page, 'page', $request);

        return $disk->response($relativePath, null, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    private function prepareLearningMediaPages(User $user, Media $media): array
    {
        abort_unless($media->status === 'active' && filled($media->path), 404);
        abort_if((int) $media->size > self::MAX_SOURCE_BYTES, 413, 'حجم فایل برای نمایش امن بیش از حد مجاز است.');

        $diskConfig = (array) config('filesystems.disks.' . $media->disk, []);
        abort_if(
            $media->disk === 'public' || ($diskConfig['visibility'] ?? null) === 'public',
            409,
            'فایل منبع روی فضای عمومی قرار دارد. مدیر باید آن را به فضای خصوصی منتقل کند؛ برای جلوگیری از افشای فایل، نمایش فعلاً فعال نشد.'
        );

        $mime = strtolower((string) $media->mime_type);
        $extension = strtolower(trim((string) ($media->extension ?: pathinfo((string) $media->original_name, PATHINFO_EXTENSION))));
        $isImage = str_starts_with($mime, 'image/');
        $isText = $this->isText($mime, $extension);

        abort_if($isText && (int) $media->size > 2_000_000, 413, 'این فایل متنی برای نمایش امن بیش از حد بزرگ است.');
        abort_if(str_starts_with($mime, 'video/'), 409, 'ویدئوی آموزشی باید از SpotPlayer پخش شود؛ فایل خام ارائه نمی‌شود.');
        abort_unless(
            $this->isPdf($mime, $extension) || $this->isOfficeDocument($extension) || $isImage || $isText,
            415,
            'این نوع فایل هنوز نمایشگر امن ندارد؛ فایل اصلی برای جلوگیری از دانلود مستقیم تحویل داده نمی‌شود.'
        );

        $sourceDisk = Storage::disk($media->disk);
        abort_unless($sourceDisk->exists($media->path), 404);

        $tempDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sheykhan-learning-view-' . bin2hex(random_bytes(8));
        if (! mkdir($tempDirectory, 0700, true) && ! is_dir($tempDirectory)) {
            throw new RuntimeException('Unable to allocate a temporary viewer directory.');
        }

        $watermark = 'SHEIKHAN | USER ' . $user->id . ' | MEDIA ' . $media->id;
        $local = Storage::disk('local');
        $relativeDirectory = '';

        try {
            $safeExtension = preg_match('/^[a-z0-9]{1,8}$/', $extension) ? $extension : 'bin';
            $sourcePath = $tempDirectory . DIRECTORY_SEPARATOR . 'source.' . $safeExtension;
            $input = $sourceDisk->readStream($media->path);
            $output = fopen($sourcePath, 'wb');
            if (! is_resource($input) || ! is_resource($output)) {
                if (is_resource($input)) fclose($input);
                if (is_resource($output)) fclose($output);
                throw new RuntimeException('Unable to read protected learning source.');
            }

            try {
                stream_copy_to_stream($input, $output);
            } finally {
                fclose($input);
                fclose($output);
            }

            $checksum = hash_file('sha256', $sourcePath);
            if (! is_string($checksum) || $checksum === '') {
                throw new RuntimeException('Unable to checksum protected learning source.');
            }

            $relativeDirectory = 'protected/learning/' . $media->id . '/' . $user->id . '/' . $checksum;
            $existingPages = $this->pageFiles($relativeDirectory);
            if ($existingPages !== []) {
                return ['directory' => $relativeDirectory, 'page_count' => count($existingPages)];
            }

            $local->deleteDirectory($relativeDirectory);
            $local->makeDirectory($relativeDirectory);

            if ($isImage) {
                $this->renderProtectedImage($sourcePath, $tempDirectory . DIRECTORY_SEPARATOR . 'page-0001.png', $watermark);
                $this->storePrivateFile($tempDirectory . DIRECTORY_SEPARATOR . 'page-0001.png', $relativeDirectory . '/page-0001.png');
            } else {
                if ($this->isOfficeDocument($extension)) {
                    $pdfSource = $this->convertOfficeToPdf($sourcePath, $tempDirectory);
                } elseif ($isText) {
                    $pdfSource = $this->convertTextToPdf($sourcePath, $tempDirectory);
                } else {
                    $signature = file_get_contents($sourcePath, false, null, 0, 5);
                    if ($signature !== '%PDF-') {
                        throw new RuntimeException('Source bytes do not have a valid PDF signature.');
                    }
                    $pdfSource = $sourcePath;
                }

                $watermarkedPdf = $tempDirectory . DIRECTORY_SEPARATOR . 'watermarked-learning.pdf';
                $this->renderWatermark($pdfSource, $watermarkedPdf, $watermark);
                $pageOutput = $tempDirectory . DIRECTORY_SEPARATOR . 'learning-rendered';
                if (! mkdir($pageOutput, 0700, true) && ! is_dir($pageOutput)) {
                    throw new RuntimeException('Unable to allocate learning page output directory.');
                }

                $binary = trim((string) config('services.pdf_viewer.binary', config('services.pdf_watermark.binary', 'gs')));
                if ($binary === '') {
                    throw new RuntimeException('PDF rendering binary is not configured.');
                }

                $process = new Process([
                    $binary,
                    '-q',
                    '-dSAFER',
                    '-dBATCH',
                    '-dNOPAUSE',
                    '-sDEVICE=png16m',
                    '-r110',
                    '-dTextAlphaBits=4',
                    '-dGraphicsAlphaBits=4',
                    '-dFirstPage=1',
                    '-dLastPage=' . (self::MAX_RENDERED_PAGES + 1),
                    '-sOutputFile=' . $pageOutput . DIRECTORY_SEPARATOR . 'page-%04d.png',
                    $watermarkedPdf,
                ]);
                $process->setTimeout(180);
                $process->mustRun();

                $generated = glob($pageOutput . DIRECTORY_SEPARATOR . 'page-*.png') ?: [];
                sort($generated, SORT_STRING);
                if ($generated === [] || count($generated) > self::MAX_RENDERED_PAGES) {
                    throw new RuntimeException('Protected learning document is empty or exceeds the secure viewer page limit.');
                }

                foreach ($generated as $generatedPage) {
                    $this->storePrivateFile($generatedPage, $relativeDirectory . '/' . basename($generatedPage));
                }
            }

            $pageFiles = $this->pageFiles($relativeDirectory);
            if ($pageFiles === []) {
                throw new RuntimeException('No secure learning pages were generated.');
            }

            return ['directory' => $relativeDirectory, 'page_count' => count($pageFiles)];
        } catch (Throwable $exception) {
            $local->deleteDirectory($relativeDirectory);
            throw $exception;
        } finally {
            $this->removeDirectory($tempDirectory);
        }
    }

    private function recordLearningMediaView(
        User $user,
        Media $media,
        ?int $resourceId,
        ?int $childId,
        ?int $pageNumber,
        string $eventType,
        Request $request,
    ): void {
        \App\Models\ProtectedLearningMediaView::query()->create([
            'user_id' => $user->id,
            'media_id' => $media->id,
            'learning_resource_id' => $resourceId,
            'child_id' => $childId,
            'page_number' => $pageNumber,
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Backwards-compatible URL: a legacy "download" link no longer returns
     * source bytes or a generated PDF. It leads to the authenticated viewer.
     */
    public function download(User $user, Order $order, ProductFile $file, Request $request): Response
    {
        // Keep legacy links functional without ever returning source or PDF
        // bytes as a download; all successful requests land in the same viewer.
        return $this->view($user, $order, $file, $request);
    }

    public function view(User $user, Order $order, ProductFile $file, Request $request): Response
    {
        [$entitlement, $media] = $this->authorizePurchasedFile($user, $order, $file);
        $this->recordView($user, $order, $file, $entitlement, null, 'open', $request);

        $mime = strtolower(trim((string) $media->mime_type));
        $extension = strtolower(trim((string) ($media->extension ?: pathinfo((string) $media->original_name, PATHINFO_EXTENSION))));
        $isText = $this->isText($mime, $extension);

        if (str_starts_with($mime, 'video/')) {
            abort(409, 'ویدئوی خریداری‌شده فقط زمانی قابل پخش است که به دورهٔ واقعی SpotPlayer متصل شده باشد. برای جلوگیری از تحویل فایل خام، دانلود مستقیم غیرفعال است؛ مدیر باید نگاشت مجاز SpotPlayer را تنظیم کند.');
        }

        if ($this->isPdf($mime, $extension) || $this->isOfficeDocument($extension) || $isText) {
            if ($isText) {
                abort_if((int) $media->size > 2_000_000, 413, 'این فایل متنی برای نمایش امن بیش از حد بزرگ است.');
            }
            try {
                $render = $this->prepareProtectedPages($user, $order, $file, $entitlement, $media, $request);

                return response()->view('commerce.protected-files.viewer', [
                    'order' => $order,
                    'file' => $file,
                    'media' => $media,
                    'product' => $file->product,
                    'referenceCode' => 'ORDER-' . $order->order_number,
                    'backUrl' => route('orders.show', $order),
                    'pageCount' => $render['page_count'],
                    'pageUrls' => collect(range(1, $render['page_count']))
                        ->map(fn (int $page): string => route('orders.files.pages', [
                            'order' => $order,
                            'file' => $file,
                            'page' => $page,
                        ])),
                    'textContent' => null,
                    'user' => $user,
                ])->header('Cache-Control', 'no-store, private')
                  ->header('Pragma', 'no-cache')
                  ->header('Referrer-Policy', 'no-referrer')
                  ->header('X-Content-Type-Options', 'nosniff');
            } catch (Throwable $exception) {
                report($exception);

                abort(503, 'نمایشگر محافظت‌شده نتوانست این فایل را آماده کند. فایل اصلی برای حفظ حقوق محتوا تحویل داده نشد.');
            }
        }

        if (str_starts_with($mime, 'image/')) {
            try {
                $render = $this->prepareProtectedPages($user, $order, $file, $entitlement, $media, $request);

                return response()->view('commerce.protected-files.viewer', [
                    'order' => $order,
                    'file' => $file,
                    'media' => $media,
                    'product' => $file->product,
                    'referenceCode' => 'ORDER-' . $order->order_number,
                    'backUrl' => route('orders.show', $order),
                    'pageCount' => $render['page_count'],
                    'pageUrls' => collect(range(1, $render['page_count']))
                        ->map(fn (int $page): string => route('orders.files.pages', [
                            'order' => $order,
                            'file' => $file,
                            'page' => $page,
                        ])),
                    'textContent' => null,
                    'user' => $user,
                ])->header('Cache-Control', 'no-store, private')
                  ->header('Pragma', 'no-cache')
                  ->header('Referrer-Policy', 'no-referrer')
                  ->header('X-Content-Type-Options', 'nosniff');
            } catch (Throwable $exception) {
                report($exception);

                abort(503, 'نمایشگر نتوانست تصویر را با واترمارک اختصاصی آماده کند؛ فایل خام تحویل داده نشد.');
            }
        }


        abort(415, 'این نوع فایل هنوز نمایشگر محافظت‌شده ندارد. برای جلوگیری از دانلود و کپی مستقیم، فایل تا آماده‌شدن نمایشگر سازگار قفل می‌ماند.');
    }

    public function page(
        User $user,
        Order $order,
        ProductFile $file,
        int $page,
        Request $request
    ): Response {
        [$entitlement, $media] = $this->authorizePurchasedFile($user, $order, $file);
        $render = $this->prepareProtectedPages($user, $order, $file, $entitlement, $media, $request);

        abort_unless($page >= 1 && $page <= $render['page_count'], 404);

        $relativePath = $render['directory'] . '/page-' . str_pad((string) $page, 4, '0', STR_PAD_LEFT) . '.png';
        $disk = Storage::disk('local');
        abort_unless($disk->exists($relativePath), 404);

        $this->recordView($user, $order, $file, $entitlement, $page, 'page', $request);

        return $disk->response($relativePath, null, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, private, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }

    /**
     * Re-check payment and entitlement on every viewer/page request. The
     * source file stays on a private disk and no direct-source URL is exposed.
     *
     * @return array{0: ProductEntitlement, 1: Media}
     */
    private function authorizePurchasedFile(User $user, Order $order, ProductFile $file): array
    {
        abort_unless((int) $order->buyer_id === (int) $user->id, 404);
        abort_unless(
            $order->status === 'paid'
                && $order->paid_at
                && $order->legal_consent_completed
                && $order->payments()
                    ->where('status', 'successful')
                    ->whereNotNull('paid_at')
                    ->where('amount', $order->total)
                    ->exists(),
            403,
            'دسترسی فایل پس از تأیید نهایی پرداخت فعال می‌شود.'
        );

        $item = $order->items()
            ->where('product_id', $file->product_id)
            ->firstOrFail();

        $entitlement = ProductEntitlement::query()
            ->where('order_item_id', $item->id)
            ->where('user_id', $user->id)
            ->where('product_id', $file->product_id)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();

        $file->loadMissing('media', 'product');
        $media = $file->media;

        abort_unless(
            $media && $media->status === 'active' && filled($media->path),
            404
        );
        abort_if((int) $media->size > self::MAX_SOURCE_BYTES, 413, 'حجم فایل برای نمایش امن بیش از حد مجاز است.');

        return [$entitlement, $media];
    }

    /**
     * Returns a private directory of per-page PNGs, with an embedded watermark
     * containing both the viewer's account ID and order number.
     *
     * @return array{directory: string, page_count: int}
     */
    private function prepareProtectedPages(
        User $user,
        Order $order,
        ProductFile $file,
        ProductEntitlement $entitlement,
        Media $media,
        Request $request
    ): array {
        $sourceDisk = Storage::disk($media->disk);
        abort_unless($sourceDisk->exists($media->path), 404);

        $extension = strtolower(trim((string) ($media->extension ?: pathinfo((string) $media->original_name, PATHINFO_EXTENSION))));
        $mime = strtolower((string) $media->mime_type);
        $isImage = str_starts_with($mime, 'image/');
        $isText = $this->isText($mime, $extension);
        abort_if($isText && (int) $media->size > 2_000_000, 413, 'این فایل متنی برای نمایش امن بیش از حد بزرگ است.');
        abort_unless(
            $this->isPdf($mime, $extension)
                || $this->isOfficeDocument($extension)
                || $isImage
                || $isText,
            415,
            'این فایل در نمایشگر محافظت‌شده پشتیبانی نمی‌شود.'
        );

        $tempDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sheykhan-view-' . bin2hex(random_bytes(8));
        if (! mkdir($tempDirectory, 0700, true) && ! is_dir($tempDirectory)) {
            throw new RuntimeException('Unable to allocate a temporary viewer directory.');
        }

        $watermark = 'SHEIKHAN | USER ' . $user->id . ' | ORDER ' . preg_replace('/[^A-Za-z0-9-]/', '', $order->order_number);
        $local = Storage::disk('local');
        $protected = null;
        $relativeDirectory = '';

        try {
            $safeExtension = preg_match('/^[a-z0-9]{1,8}$/', $extension) ? $extension : 'bin';
            $sourcePath = $tempDirectory . DIRECTORY_SEPARATOR . 'source.' . $safeExtension;
            $input = $sourceDisk->readStream($media->path);
            $output = fopen($sourcePath, 'wb');

            if (! is_resource($input) || ! is_resource($output)) {
                if (is_resource($input)) fclose($input);
                if (is_resource($output)) fclose($output);
                throw new RuntimeException('Unable to read the protected source file.');
            }

            try {
                stream_copy_to_stream($input, $output);
            } finally {
                fclose($input);
                fclose($output);
            }

            $sourceChecksum = hash_file('sha256', $sourcePath);
            if (! is_string($sourceChecksum) || $sourceChecksum === '') {
                throw new RuntimeException('Unable to checksum the protected source file.');
            }

            $relativeDirectory = 'protected/products/' . $file->product_id . '/' . $entitlement->id . '/' . $file->id . '-' . $sourceChecksum;
            $protected = ProtectedFile::query()->firstOrNew([
                'product_file_id' => $file->id,
                'entitlement_id' => $entitlement->id,
            ]);

            $existingPages = $protected->exists && $protected->status === 'ready'
                && hash_equals((string) $protected->source_checksum, $sourceChecksum)
                ? $this->pageFiles($relativeDirectory)
                : [];

            if ($existingPages !== []) {
                return ['directory' => $relativeDirectory, 'page_count' => count($existingPages)];
            }

            $protected->fill([
                'source_checksum' => $sourceChecksum,
                'generated_path' => $relativeDirectory,
                'watermark_text' => $watermark,
                'watermark_type' => 'user-order-raster',
                'status' => 'processing',
            ])->save();

            $local->deleteDirectory($relativeDirectory);
            $local->makeDirectory($relativeDirectory);

            if ($isImage) {
                $this->renderProtectedImage($sourcePath, $tempDirectory . DIRECTORY_SEPARATOR . 'page-0001.png', $watermark);
                $this->storePrivateFile(
                    $tempDirectory . DIRECTORY_SEPARATOR . 'page-0001.png',
                    $relativeDirectory . '/page-0001.png'
                );
            } else {
                $pdfSource = $sourcePath;

                if ($this->isOfficeDocument($extension)) {
                    $pdfSource = $this->convertOfficeToPdf($sourcePath, $tempDirectory);
                } elseif ($isText) {
                    $pdfSource = $this->convertTextToPdf($sourcePath, $tempDirectory);
                } else {
                    $signature = file_get_contents($pdfSource, false, null, 0, 5);
                    if ($signature !== '%PDF-') {
                        throw new RuntimeException('Source bytes do not have a valid PDF signature.');
                    }
                }

                $watermarkedPdf = $tempDirectory . DIRECTORY_SEPARATOR . 'watermarked.pdf';
                $this->renderWatermark($pdfSource, $watermarkedPdf, $watermark);

                $pageOutput = $tempDirectory . DIRECTORY_SEPARATOR . 'rendered';
                if (! mkdir($pageOutput, 0700, true) && ! is_dir($pageOutput)) {
                    throw new RuntimeException('Unable to allocate PDF page output directory.');
                }

                $binary = trim((string) config('services.pdf_viewer.binary', config('services.pdf_watermark.binary', 'gs')));
                abort_if($binary === '', 503, 'موتور نمایش PDF روی سرور پیکربندی نشده است.');

                $process = new Process([
                    $binary,
                    '-q',
                    '-dSAFER',
                    '-dBATCH',
                    '-dNOPAUSE',
                    '-sDEVICE=png16m',
                    '-r110',
                    '-dTextAlphaBits=4',
                    '-dGraphicsAlphaBits=4',
                    '-dFirstPage=1',
                    '-dLastPage=' . (self::MAX_RENDERED_PAGES + 1),
                    '-sOutputFile=' . $pageOutput . DIRECTORY_SEPARATOR . 'page-%04d.png',
                    $watermarkedPdf,
                ]);
                $process->setTimeout(180);
                $process->mustRun();

                $generated = glob($pageOutput . DIRECTORY_SEPARATOR . 'page-*.png') ?: [];
                sort($generated, SORT_STRING);

                if ($generated === []) {
                    throw new RuntimeException('No page images were generated from the protected PDF.');
                }

                if (count($generated) > self::MAX_RENDERED_PAGES) {
                    throw new RuntimeException('This document exceeds the secure viewer page limit.');
                }

                foreach ($generated as $generatedPage) {
                    $basename = basename($generatedPage);
                    $this->storePrivateFile($generatedPage, $relativeDirectory . '/' . $basename);
                }
            }

            $pageFiles = $this->pageFiles($relativeDirectory);
            if ($pageFiles === []) {
                throw new RuntimeException('The protected viewer did not produce a usable page.');
            }

            $protected->forceFill([
                'generated_path' => $relativeDirectory,
                'generated_at' => now(),
                'status' => 'ready',
            ])->save();

            return ['directory' => $relativeDirectory, 'page_count' => count($pageFiles)];
        } catch (Throwable $exception) {
            if ($protected && $protected->exists) {
                $protected->forceFill(['status' => 'failed'])->save();
            }

            $local->deleteDirectory($relativeDirectory);
            throw $exception;
        } finally {
            $this->removeDirectory($tempDirectory);
        }
    }

    private function convertOfficeToPdf(string $sourcePath, string $tempDirectory): string
    {
        $binary = trim((string) config('services.pdf_viewer.office_binary', 'soffice'));
        if ($binary === '') {
            throw new RuntimeException('Office document conversion binary is not configured.');
        }

        $profile = $tempDirectory . DIRECTORY_SEPARATOR . 'office-profile';
        $process = new Process([
            $binary,
            '-env:UserInstallation=file://' . $profile,
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $tempDirectory,
            $sourcePath,
        ]);
        $process->setTimeout(120);
        $process->mustRun();

        $converted = $tempDirectory . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
        if (! is_file($converted) || filesize($converted) < 6) {
            throw new RuntimeException('Office document converter did not produce a PDF.');
        }

        $signature = file_get_contents($converted, false, null, 0, 5);
        if ($signature !== '%PDF-') {
            throw new RuntimeException('Office converter output is not a valid PDF.');
        }

        return $converted;
    }

    private function renderWatermark(string $source, string $output, string $watermark): void
    {
        $binary = trim((string) config('services.pdf_watermark.binary', 'gs'));
        if ($binary === '') {
            throw new RuntimeException('PDF watermark binary is not configured.');
        }

        $escaped = str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', ' ', ' '],
            $watermark
        );

        $postScript = '<< /EndPage { 2 eq { pop false } { pop gsave 0.72 setgray /Helvetica findfont 12 scalefont setfont 36 24 moveto (' .
            $escaped .
            ') show grestore true } ifelse } bind >> setpagedevice';

        $process = new Process([
            $binary,
            '-q',
            '-dSAFER',
            '-dBATCH',
            '-dNOPAUSE',
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-sOutputFile=' . $output,
            '-c',
            $postScript,
            '-f',
            $source,
        ]);
        $process->setTimeout(120);
        $process->mustRun();

        if (! is_file($output) || filesize($output) < 16) {
            throw new RuntimeException('Watermarked PDF output was not produced.');
        }
    }

    private function renderProtectedImage(string $source, string $output, string $watermark): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagepng')) {
            throw new RuntimeException('GD extension is required for protected image viewing.');
        }

        $bytes = file_get_contents($source);
        $image = is_string($bytes) ? @imagecreatefromstring($bytes) : false;
        if ($image === false) {
            throw new RuntimeException('Image source is invalid or unsupported by GD.');
        }

        try {
            $width = imagesx($image);
            $height = imagesy($image);
            $text = substr($watermark, 0, 150);
            $color = imagecolorallocatealpha($image, 65, 65, 65, 72);

            for ($y = 18; $y < $height; $y += 110) {
                for ($x = 10 + (($y / 110) % 2) * 120; $x < $width; $x += 300) {
                    imagestring($image, 3, $x, $y, $text, $color);
                }
            }

            if (! imagepng($image, $output, 6)) {
                throw new RuntimeException('Watermarked image output was not written.');
            }
        } finally {
            imagedestroy($image);
        }
    }

    private function storePrivateFile(string $sourcePath, string $targetPath): void
    {
        $stream = fopen($sourcePath, 'rb');
        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to open rendered page image.');
        }

        try {
            if (! Storage::disk('local')->put($targetPath, $stream, ['visibility' => 'private'])) {
                throw new RuntimeException('Unable to persist rendered page image.');
            }
        } finally {
            fclose($stream);
        }
    }

    /** @return list<string> */
    private function pageFiles(string $directory): array
    {
        $files = Storage::disk('local')->files($directory);
        $files = array_values(array_filter(
            $files,
            static fn (string $path): bool => preg_match('~/page-\d{4}\.png$~', $path) === 1
        ));
        sort($files, SORT_STRING);

        return $files;
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $entry) {
            $entry->isDir() && ! $entry->isLink()
                ? @rmdir($entry->getPathname())
                : @unlink($entry->getPathname());
        }

        @rmdir($directory);
    }

    private function recordView(
        User $user,
        Order $order,
        ProductFile $file,
        ProductEntitlement $entitlement,
        ?int $pageNumber,
        string $eventType,
        Request $request,
    ): void {
        ProtectedFileView::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_file_id' => $file->id,
            'entitlement_id' => $entitlement->id,
            'page_number' => $pageNumber,
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'viewed_at' => now(),
        ]);

    }


    private function convertTextToPdf(string $sourcePath, string $tempDirectory): string
    {
        $text = file_get_contents($sourcePath);
        if (! is_string($text)) {
            throw new RuntimeException('Unable to read text document for secure rendering.');
        }

        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $htmlPath = $tempDirectory . DIRECTORY_SEPARATOR . 'text-source.html';
        $html = '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><style>' .
            '@page{size:A4;margin:14mm;}body{font-family:sans-serif;direction:rtl;unicode-bidi:plaintext;color:#111;}' .
            'h1{font-size:13pt;margin:0 0 16px}pre{font-family:sans-serif;font-size:10pt;line-height:1.7;white-space:pre-wrap;overflow-wrap:anywhere;direction:rtl;unicode-bidi:plaintext;}' .
            '</style></head><body><h1>محتوای متنی محافظت‌شدهٔ شیخان</h1><pre>' . $escaped . '</pre></body></html>';

        if (file_put_contents($htmlPath, $html, LOCK_EX) === false) {
            throw new RuntimeException('Unable to prepare text document for secure rendering.');
        }

        return $this->convertOfficeToPdf($htmlPath, $tempDirectory);
    }

    private function isText(string $mime, string $extension): bool
    {
        return in_array($extension, ['txt', 'csv', 'md'], true)
            || in_array($mime, ['text/plain', 'text/csv', 'text/markdown'], true);
    }

    private function isPdf(string $mime, string $extension): bool
    {
        return $mime === 'application/pdf' || $extension === 'pdf';
    }

    private function isOfficeDocument(string $extension): bool
    {
        return in_array($extension, ['doc', 'docx', 'odt', 'rtf', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'odp'], true);
    }

}
