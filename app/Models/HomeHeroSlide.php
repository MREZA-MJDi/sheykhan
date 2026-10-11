<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeHeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot',
        'media_id',
        'mobile_media_id',
        'title',
        'description',
        'cta_label',
        'cta_url',
        'crop_x',
        'crop_y',
        'mobile_crop_x',
        'mobile_crop_y',
        'is_active',
        'sort_order',
        'updated_by',
    ];

    protected $casts = [
        'slot' => 'integer',
        'media_id' => 'integer',
        'mobile_media_id' => 'integer',
        'crop_x' => 'integer',
        'crop_y' => 'integer',
        'mobile_crop_x' => 'integer',
        'mobile_crop_y' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'updated_by' => 'integer',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mobileMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mobile_media_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
