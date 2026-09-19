<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaService
{
    public function upload(UploadedFile $file, ?Model $attachable = null, array $options = []): Media
    {
        $disk = $options['disk'] ?? config('filesystems.default', 'local');
        $directory = trim($options['directory'] ?? 'media', '/');
        $collection = $options['collection'] ?? 'default';
        $visibility = $options['visibility'] ?? 'private';

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $fileName = (string) Str::uuid().($extension ? '.'.$extension : '');
        $path = $file->storeAs($directory, $fileName, $disk);

        $media = Media::create([
            'uploaded_by' => auth()->id(),
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension ?: null,
            'size' => (int) ($file->getSize() ?: 0),
            'checksum' => $file->getRealPath() ? hash_file('sha256', $file->getRealPath()) : null,
            'visibility' => $visibility,
            'collection' => $collection,
            'metadata' => $options['metadata'] ?? [],
            'status' => 'active',
        ]);

        if ($attachable) {
            $this->attach(
                $media,
                $attachable,
                $collection,
                (int) ($options['sort_order'] ?? 0),
                (bool) ($options['is_featured'] ?? false),
            );
        }

        return $media;
    }

    public function attach(Media $media, Model $attachable, string $collection = 'default', int $sortOrder = 0, bool $isFeatured = false): void
    {
        abort_unless(method_exists($attachable, 'media'), 500, 'The attachable model must use the HasMedia trait.');

        $attachable->media()->syncWithoutDetaching([
            $media->id => [
                'collection' => $collection,
                'sort_order' => $sortOrder,
                'is_featured' => $isFeatured,
            ],
        ]);
    }

    public function detach(Media $media, Model $attachable): void
    {
        abort_unless(method_exists($attachable, 'media'), 500, 'The attachable model must use the HasMedia trait.');
        $attachable->media()->detach($media->id);
    }

    public function replace(Media $oldMedia, UploadedFile $newFile, ?Model $attachable = null, array $options = []): Media
    {
        $newMedia = $this->upload($newFile, $attachable, $options);

        if ($attachable) {
            $this->detach($oldMedia, $attachable);
        }

        if ($oldMedia->attachments()->doesntExist()) {
            $this->delete($oldMedia);
        }

        return $newMedia;
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    public function url(Media $media): ?string
    {
        if ($media->visibility !== 'public') {
            return null;
        }

        return Storage::disk($media->disk)->url($media->path);
    }

    public function download(Media $media): StreamedResponse
    {
        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        return Storage::disk($media->disk)->download(
            $media->path,
            $media->original_name,
            ['Content-Type' => $media->mime_type ?: 'application/octet-stream'],
        );
    }
}
