<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by','disk','path','original_name','file_name','mime_type',
        'extension','size','checksum','visibility','collection','metadata','status',
    ];

    protected $casts = ['metadata' => 'array'];

    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function attachments(): HasMany { return $this->hasMany(MediaAttachment::class); }

    public function url(): ?string
    {
        if ($this->visibility !== 'public') {
            return null;
        }

        // Every local filesystem disk must use the application media endpoint.
        // A direct /storage URL depends on a manually-created public symlink and
        // can silently break public images after deployment or disk changes.
        $driver = config("filesystems.disks.{$this->disk}.driver");

        if ($driver === 'local') {
            return route('media.public', $this);
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
