<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CourseVideoStreamService
{
    public function stream(Media $media, Request $request): StreamedResponse
    {
        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($media->path), 404);

        $size = (int) $disk->size($media->path);
        $range = $this->resolveRange($request->headers->get('Range'), $size);

        if ($range === false) {
            return response()->stream(
                static fn () => null,
                416,
                [
                    'Content-Range' => 'bytes */' . $size,
                    'Accept-Ranges' => 'bytes',
                    'Cache-Control' => 'private, no-store, max-age=0',
                    'X-Content-Type-Options' => 'nosniff',
                ],
            );
        }

        [$start, $end] = $range ?? [0, max(0, $size - 1)];
        $length = $size === 0 ? 0 : ($end - $start + 1);
        $partial = $range !== null;

        $headers = [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_INLINE,
                $this->safeFilename($media->original_name),
                'course-video.mp4',
            ),
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'X-Accel-Buffering' => 'no',
        ];

        if ($partial) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        return response()->stream(function () use ($disk, $media, $start, $length): void {
            $stream = $disk->readStream($media->path);

            if (! is_resource($stream)) {
                return;
            }

            try {
                $this->skipBytes($stream, $start);

                $remaining = $length;
                while ($remaining > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(1024 * 1024, $remaining));

                    if ($chunk === false || $chunk === '') {
                        break;
                    }

                    echo $chunk;
                    $remaining -= strlen($chunk);

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();
                }
            } finally {
                fclose($stream);
            }
        }, $partial ? 206 : 200, $headers);
    }

    /**
     * @return array{0:int,1:int}|null|false
     */
    private function resolveRange(?string $header, int $size): array|null|false
    {
        if ($header === null || $header === '') {
            return null;
        }

        if ($size < 1 || ! preg_match('/^bytes=(\d*)-(\d*)$/', trim($header), $matches)) {
            return false;
        }

        $first = $matches[1];
        $last = $matches[2];

        if ($first === '' && $last === '') {
            return false;
        }

        if ($first === '') {
            $suffixLength = (int) $last;
            if ($suffixLength < 1) {
                return false;
            }

            return [max(0, $size - $suffixLength), $size - 1];
        }

        $start = (int) $first;
        $end = $last === '' ? $size - 1 : min((int) $last, $size - 1);

        if ($start >= $size || $start > $end) {
            return false;
        }

        return [$start, $end];
    }

    /** @param resource $stream */
    private function skipBytes($stream, int $bytes): void
    {
        if ($bytes < 1) {
            return;
        }

        $metadata = stream_get_meta_data($stream);
        if (($metadata['seekable'] ?? false) && fseek($stream, $bytes) === 0) {
            return;
        }

        while ($bytes > 0 && ! feof($stream)) {
            $chunk = fread($stream, min(1024 * 1024, $bytes));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $bytes -= strlen($chunk);
        }
    }

    private function safeFilename(?string $filename): string
    {
        $filename = basename(str_replace(["\\", "\r", "\n", '"'], ['/', '', '', ''], (string) $filename));

        return $filename !== '' ? $filename : 'course-video';
    }
}
