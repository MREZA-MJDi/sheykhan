<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by','disk','path','original_name','file_name','mime_type',
        'extension','size','checksum','visibility','collection','metadata','status',
    ];

    protected $casts = ['metadata' => 'array'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    public function academies(): MorphToMany
    {
        return $this->morphedByMany(Academy::class, 'mediable', 'media_attachments')
            ->withPivot(['collection','sort_order','is_featured'])
            ->withTimestamps();
    }

    public function url(): ?string
    {
        if ($this->visibility !== 'public') {
            return null;
        }

        return \Storage::disk($this->disk)->url($this->path);
    }
}
