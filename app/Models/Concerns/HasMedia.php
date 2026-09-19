<?php

namespace App\Models\Concerns;

use App\Models\Media;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMedia
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'mediable');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'media_attachments')
            ->withPivot(['collection','sort_order','is_featured'])
            ->withTimestamps();
    }
}
