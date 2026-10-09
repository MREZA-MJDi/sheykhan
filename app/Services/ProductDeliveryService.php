<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Order;
use App\Models\ProductDownload;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\ProtectedFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Throwable;

final class ProductDeliveryService
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function download(User $user, Order $order, ProductFile $file, Request $request): Response
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
            403
        );

        $item = $order->items()->where('product_id', $file->product_id)->firstOrFail();
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
        abort_unless($media && $media->status === 'active' && filled($media->path), 404);

        $isPdf = strtolower((string) $media->extension) === 'pdf'
            || strtolower((string) $media->mime_type) === 'application/pdf';

        // A purchased PDF must never fall back to the unmarked source.
        if ($isPdf || $file->requires_watermark) {
            abort_unless($isPdf, 503, 'موتور محافظت این نوع فایل برای تحویل امن آماده نیست.');

            return $this->downloadWatermarkedPdf($user, $order, $file, $entitlement, $media, $request);
        }

        $this->recordDownload($entitlement, $file, $request);

        return $this->mediaService->download($media);
    }

    private function downloadWatermarkedPdf(
        User $user,
        Order $order,
        ProductFile $file,
        ProductEntitlement $entitlement,
        Media $media,
        Request $request
    ): Response {
        $sourceDisk = Storage::disk($media->disk);
        abort_unless($sourceDisk->exists($media->path), 404);

        $tempSource = tempnam(sys_get_temp_dir(), 'sheykhan-source-');
        $tempOutput = tempnam(sys_get_temp_dir(), 'sheykhan-watermark-');

        if ($tempSource === false || $tempOutput === false) {
            if (is_string($tempSource) && is_file($tempSource)) @unlink($tempSource);
            if (is_string($tempOutput) && is_file($tempOutput)) @unlink($tempOutput);
            throw new RuntimeException('Unable to allocate temporary files for PDF protection.');
        }

        try {
            $stream = $sourceDisk->readStream($media->path);
            $target = fopen($tempSource, 'wb');
            if (! is_resource($stream) || ! is_resource($target)) {
                if (is_resource($stream)) fclose($stream);
                if (is_resource($target)) fclose($target);
                throw new RuntimeException('Unable to read purchased source PDF.');
            }
            stream_copy_to_stream($stream, $target);
            fclose($stream);
            fclose($target);

            $signature = file_get_contents($tempSource, false, null, 0, 5);
            if ($signature !== '%PDF-') {
                throw new RuntimeException('Purchased source is not a valid PDF.');
            }

            $checksum = hash_file('sha256', $tempSource);
            if (! $checksum) {
                throw new RuntimeException('Unable to checksum purchased PDF.');
            }

            $watermark = 'SHEIKHAN | USER ' . $user->id . ' | ORDER ' . preg_replace('/[^A-Za-z0-9-]/', '', $order->order_number);
            $local = Storage::disk('local');
            $protected = ProtectedFile::query()->firstOrNew([
                'product_file_id' => $file->id,
                'entitlement_id' => $entitlement->id,
            ]);

            if (
                $protected->exists
                && $protected->status === 'ready'
                && hash_equals((string) $protected->source_checksum, $checksum)
                && filled($protected->generated_path)
                && $local->exists($protected->generated_path)
            ) {
                $this->recordDownload($entitlement, $file, $request);
                $protected->increment('download_count');

                return $local->download($protected->generated_path, $this->downloadName($file), $this->privateDownloadHeaders());
            }

            $relativePath = 'protected/products/' . $file->product_id . '/' . $entitlement->id . '/' . $file->id . '-' . $checksum . '.pdf';
            $protected->fill([
                'source_checksum' => $checksum,
                'generated_path' => $relativePath,
                'watermark_text' => $watermark,
                'watermark_type' => 'text',
                'status' => 'processing',
            ])->save();

            try {
                $this->renderWatermark($tempSource, $tempOutput, $watermark);

                $outputStream = fopen($tempOutput, 'rb');
                if (! is_resource($outputStream)) {
                    throw new RuntimeException('Watermark engine did not produce an output PDF.');
                }
                try {
                    $written = $local->writeStream($relativePath, $outputStream, ['visibility' => 'private']);
                } finally {
                    fclose($outputStream);
                }

                $outputStream = $local->readStream($relativePath);
                $outputSignature = is_resource($outputStream) ? fread($outputStream, 5) : false;
                if (is_resource($outputStream)) fclose($outputStream);

                if (! $written || $outputSignature !== '%PDF-') {
                    $local->delete($relativePath);
                    throw new RuntimeException('Watermarked output failed validation.');
                }

                $protected->forceFill(['generated_at' => now(), 'status' => 'ready'])->save();
            } catch (Throwable $exception) {
                $protected->forceFill(['status' => 'failed'])->save();
                $local->delete($relativePath);
                throw $exception;
            }

            $this->recordDownload($entitlement, $file, $request);
            $protected->increment('download_count');

            return $local->download($relativePath, $this->downloadName($file), $this->privateDownloadHeaders());
        } catch (Throwable $exception) {
            report($exception);
            abort(503, 'ساخت نسخهٔ واترمارک‌شده ممکن نشد؛ فایل اصلی برای حفظ کپی‌رایت تحویل داده نشد.');
        } finally {
            if (is_file($tempSource)) @unlink($tempSource);
            if (is_file($tempOutput)) @unlink($tempOutput);
        }
    }

    private function renderWatermark(string $source, string $output, string $watermark): void
    {
        $binary = trim((string) config('services.pdf_watermark.binary', 'gs'));
        if ($binary === '') {
            throw new RuntimeException('PDF_WATERMARK_BINARY is not configured.');
        }

        $escaped = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $watermark);
        $postScript = '<< /EndPage { 2 eq { pop false } { pop gsave 0.72 setgray /Helvetica findfont 12 scalefont setfont 36 24 moveto (' .
            $escaped .
            ') show grestore true } { true } ifelse } bind >> setpagedevice';

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
            throw new RuntimeException('PDF watermark output was not produced.');
        }
    }

    private function recordDownload(ProductEntitlement $entitlement, ProductFile $file, Request $request): void
    {
        ProductDownload::query()->create([
            'entitlement_id' => $entitlement->id,
            'product_file_id' => $file->id,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'downloaded_at' => now(),
        ]);
    }

    private function downloadName(ProductFile $file): string
    {
        $title = (string) ($file->product?->title ?? 'sheykhan-product');
        $safe = preg_replace('/[^\pL\pN._ -]+/u', '', $title) ?: 'sheykhan-product';

        return trim($safe) . '.pdf';
    }

    private function privateDownloadHeaders(): array
    {
        return [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
