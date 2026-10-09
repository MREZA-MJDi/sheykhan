<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'academy_id',
        'media_id',
        'slot',
        'title',
        'description',
        'cta_label',
        'cta_url',
        'sort_order',
        'crop_x',
        'crop_y',
        'is_active',
    ];

    protected $casts = [
        'slot' => 'integer',
        'sort_order' => 'integer',
        'crop_x' => 'integer',
        'crop_y' => 'integer',
        'is_active' => 'boolean',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
