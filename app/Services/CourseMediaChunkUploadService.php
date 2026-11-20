<?php

namespace App\Services;

use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

final class CourseMediaChunkUploadService
{
    public const CHUNK_BYTES = 5 * 1024 * 1024;

    private const MAX_UPLOAD_BYTES = 536870912;

    private const SESSION_TTL_SECONDS = 86400;

    public function __construct(private readonly CourseMediaUploadService $uploads)
    {
    }

    /**
     * Store one idempotent part. The upload session is bound to both the
     * authenticated uploader and the exact course being edited.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function storeChunk(
        User $user,
        Course $course,
        array $data,
        UploadedFile $chunk,
    ): array {
        $uploadId = strtolower((string) $data['upload_id']);
        abort_unless(Str::isUuid($uploadId), 404);

        $fileName = $this->safeFilename((string) $data['file_name']);
        $fileSize = (int) $data['file_size'];
        $chunkIndex = (int) $data['chunk_index'];
        $totalChunks = (int) $data['total_chunks'];
        $collection = (string) $data['collection'];

        $this->assertUploadShape($fileSize, $chunkIndex, $totalChunks);

        if ($collection === 'course-cover' && ! $user->hasRole('academy-owner')) {
            abort(403);
        }

        $disk = Storage::disk('local');
        $base = $this->basePath($user, $course, $uploadId);
        $metadataPath = $base . '/metadata.json';

        $metadata = [
            'upload_id' => $uploadId,
            'user_id' => (int) $user->id,
            'course_id' => (int) $course->id,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => filled($data['mime_type'] ?? null) ? (string) $data['mime_type'] : null,
            'collection' => $collection,
            'total_chunks' => $totalChunks,
            'created_at' => time(),
            'expires_at' => time() + self::SESSION_TTL_SECONDS,
        ];

        if ($disk->exists($metadataPath)) {
            $existing = json_decode((string) $disk->get($metadataPath), true);

            if (! is_array($existing) || ! $this->sameSession($existing, $metadata)) {
                abort(409, 'این شناسه آپلود قبلاً برای فایل یا دوره دیگری استفاده شده است.');
            }

            $metadata = $existing;
            $this->assertNotExpired($disk, $base, $metadata);
        } else {
            $disk->makeDirectory($base . '/chunks');
            if (! $disk->put($metadataPath, json_encode($metadata, JSON_THROW_ON_ERROR))) {
                throw new RuntimeException('Could not create upload session metadata.');
            }
        }

        $expectedChunkBytes = $this->expectedChunkBytes($fileSize, $chunkIndex, $totalChunks);
        if ((int) $chunk->getSize() !== $expectedChunkBytes) {
            throw ValidationException::withMessages([
                'chunk' => 'اندازه بخش ارسالی با اندازه مورد انتظار فایل تطابق ندارد؛ ارسال را دوباره انجام بده.',
            ]);
        }

        $chunkPath = $base . '/chunks/' . sprintf('%06d', $chunkIndex) . '.part';
        $stream = fopen($chunk->getRealPath(), 'rb');

        if (! is_resource($stream)) {
            throw new RuntimeException('Could not read uploaded chunk.');
        }

        try {
            $stored = $disk->put($chunkPath, $stream);
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            throw new RuntimeException('Could not persist uploaded chunk.');
        }

        return $this->status($user, $course, $uploadId);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(User $user, Course $course, string $uploadId): array
    {
        [$base, $metadata] = $this->session($user, $course, $uploadId);
        $disk = Storage::disk('local');
        $received = [];

        for ($index = 0; $index < (int) $metadata['total_chunks']; $index++) {
            $path = $base . '/chunks/' . sprintf('%06d', $index) . '.part';
            if ($disk->exists($path)) {
                $received[] = $index;
            }
        }

        return [
            'upload_id' => $metadata['upload_id'],
            'file_name' => $metadata['file_name'],
            'file_size' => (int) $metadata['file_size'],
            'collection' => $metadata['collection'],
            'total_chunks' => (int) $metadata['total_chunks'],
            'chunk_size' => self::CHUNK_BYTES,
            'received_chunks' => $received,
            'expires_at' => (int) $metadata['expires_at'],
        ];
    }

    public function complete(
        User $user,
        Course $course,
        string $uploadId,
        bool $allowPublicCover,
    ): Media {
        [$base, $metadata] = $this->session($user, $course, $uploadId);
        $disk = Storage::disk('local');

        $parts = [];
        for ($index = 0; $index < (int) $metadata['total_chunks']; $index++) {
            $path = $base . '/chunks/' . sprintf('%06d', $index) . '.part';
            if (! $disk->exists($path)) {
                throw ValidationException::withMessages([
                    'upload' => 'همه بخش‌های فایل دریافت نشده‌اند؛ بخش‌های باقی‌مانده را ارسال و دوباره تلاش کن.',
                ]);
            }
            $parts[] = $path;
        }

        $assembledPath = $base . '/assembled.tmp';
        $absolutePath = $disk->path($assembledPath);
        $output = fopen($absolutePath, 'wb');

        if (! is_resource($output)) {
            throw new RuntimeException('Could not create assembled upload file.');
        }

        try {
            foreach ($parts as $partPath) {
                $input = $disk->readStream($partPath);
                if (! is_resource($input)) {
                    throw new RuntimeException('Could not read an uploaded chunk.');
                }

                try {
                    if (stream_copy_to_stream($input, $output) === false) {
                        throw new RuntimeException('Could not assemble uploaded chunks.');
                    }
                } finally {
                    fclose($input);
                }
            }
        } catch (Throwable $exception) {
            fclose($output);
            $disk->delete($assembledPath);
            throw $exception;
        }

        fclose($output);

        try {
            clearstatcache(true, $absolutePath);
            if (! is_file($absolutePath) || (int) filesize($absolutePath) !== (int) $metadata['file_size']) {
                throw ValidationException::withMessages([
                    'upload' => 'اندازه فایل نهایی با فایل انتخاب‌شده یکسان نیست؛ آپلود را دوباره ادامه بده.',
                ]);
            }

            $extension = strtolower(pathinfo((string) $metadata['file_name'], PATHINFO_EXTENSION));
            $file = new UploadedFile(
                $absolutePath,
                (string) $metadata['file_name'],
                null,
                UPLOAD_ERR_OK,
                true,
            );

            Validator::make(
                ['media' => $file],
                ['media' => CourseMediaRequest::mediaRules((string) $metadata['collection'], $extension)],
            )->validate();

            $media = $this->uploads->storeUploaded(
                $course,
                $file,
                (string) $metadata['collection'],
                $allowPublicCover,
                (int) $user->id,
            );

            $disk->deleteDirectory($base);

            return $media;
        } finally {
            if ($disk->exists($assembledPath)) {
                $disk->delete($assembledPath);
            }
        }
    }

    public function abort(User $user, Course $course, string $uploadId): void
    {
        [$base] = $this->session($user, $course, $uploadId);
        Storage::disk('local')->deleteDirectory($base);
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function session(User $user, Course $course, string $uploadId): array
    {
        abort_unless(Str::isUuid($uploadId), 404);

        $base = $this->basePath($user, $course, strtolower($uploadId));
        $metadataPath = $base . '/metadata.json';
        $disk = Storage::disk('local');

        abort_unless($disk->exists($metadataPath), 404);

        $metadata = json_decode((string) $disk->get($metadataPath), true);
        abort_unless(
            is_array($metadata)
                && (int) ($metadata['user_id'] ?? 0) === (int) $user->id
                && (int) ($metadata['course_id'] ?? 0) === (int) $course->id,
            404,
        );

        $this->assertNotExpired($disk, $base, $metadata);

        return [$base, $metadata];
    }

    private function assertNotExpired($disk, string $base, array $metadata): void
    {
        if ((int) ($metadata['expires_at'] ?? 0) < time()) {
            $disk->deleteDirectory($base);
            abort(410, 'مهلت ادامه این آپلود تمام شده است؛ فایل را دوباره انتخاب کن.');
        }
    }

    private function assertUploadShape(int $fileSize, int $chunkIndex, int $totalChunks): void
    {
        if (
            $fileSize < 1
            || $fileSize > self::MAX_UPLOAD_BYTES
            || $totalChunks !== (int) ceil($fileSize / self::CHUNK_BYTES)
            || $chunkIndex < 0
            || $chunkIndex >= $totalChunks
        ) {
            throw ValidationException::withMessages([
                'upload' => 'مشخصات بخش‌بندی فایل معتبر نیست.',
            ]);
        }
    }

    private function expectedChunkBytes(int $fileSize, int $chunkIndex, int $totalChunks): int
    {
        if ($chunkIndex < $totalChunks - 1) {
            return self::CHUNK_BYTES;
        }

        return $fileSize - (self::CHUNK_BYTES * ($totalChunks - 1));
    }

    private function basePath(User $user, Course $course, string $uploadId): string
    {
        return 'course-upload-chunks/' . $user->id . '/' . $course->id . '/' . strtolower($uploadId);
    }

    private function safeFilename(string $fileName): string
    {
        $fileName = basename(str_replace(["\\", "\r", "\n", '/', '"'], ['', '', '', '/', ''], $fileName));

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            throw ValidationException::withMessages([
                'file_name' => 'نام فایل معتبر نیست.',
            ]);
        }

        return $fileName;
    }

    private function sameSession(array $existing, array $requested): bool
    {
        foreach (['upload_id', 'user_id', 'course_id', 'file_name', 'file_size', 'collection', 'total_chunks'] as $key) {
            if ((string) ($existing[$key] ?? '') !== (string) ($requested[$key] ?? '')) {
                return false;
            }
        }

        return true;
    }
}
