<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class PruneCourseMediaUploads extends Command
{
    protected $signature = 'course-media-uploads:prune
        {--force : Delete expired upload sessions; without this option only report them}';

    protected $description = 'Prune expired temporary chunks from resumable course-media uploads.';

    private const SESSION_TTL_SECONDS = 86400;

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $metadataFiles = $disk->allFiles('course-upload-chunks');
        $expiredDirectories = [];

        foreach ($metadataFiles as $metadataPath) {
            if (basename($metadataPath) !== 'metadata.json') {
                continue;
            }

            $metadata = json_decode((string) $disk->get($metadataPath), true);
            $expiresAt = is_array($metadata)
                ? (int) ($metadata['expires_at'] ?? 0)
                : 0;

            if ($expiresAt < 1) {
                // A damaged session file is only removed after its own
                // modification time has exceeded the normal 24-hour TTL.
                $expiresAt = (int) $disk->lastModified($metadataPath) + self::SESSION_TTL_SECONDS;
            }

            if ($expiresAt <= time()) {
                $expiredDirectories[dirname($metadataPath)] = true;
            }
        }

        $directories = array_keys($expiredDirectories);
        $execute = (bool) $this->option('force');

        if ($execute) {
            foreach ($directories as $directory) {
                $disk->deleteDirectory($directory);
            }
        }

        $count = count($directories);
        $this->components->info(
            $execute
                ? number_format($count) . ' نشست آپلود منقضی و فایل‌های موقت آن پاک‌سازی شد.'
                : number_format($count) . ' نشست منقضی پیدا شد؛ برای پاک‌سازی واقعی --force را اضافه کن.'
        );

        return self::SUCCESS;
    }
}
