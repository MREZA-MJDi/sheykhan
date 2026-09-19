<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id','mediable_type','mediable_id','collection','sort_order','is_featured'
    ];

    protected $casts = ['is_featured' => 'boolean'];

    public function media(): BelongsTo { return $this->belongsTo(Media::class); }
    public function mediable(): MorphTo { return $this->morphTo(); }
}
