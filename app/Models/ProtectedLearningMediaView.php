<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProtectedLearningMediaView extends Model
{
    protected $fillable = [
        'user_id',
        'media_id',
        'academy_id',
        'learning_resource_id',
        'child_id',
        'page_number',
        'event_type',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'academy_id' => 'integer',
            'learning_resource_id' => 'integer',
            'child_id' => 'integer',
            'page_number' => 'integer',
            'viewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
