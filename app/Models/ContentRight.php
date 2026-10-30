<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentRight extends Model
{
    protected $fillable = [
        'media_id',
        'created_by',
        'title',
        'rights_holder',
        'source_type',
        'license_name',
        'license_reference',
        'source_url',
        'territory',
        'valid_from',
        'valid_until',
        'permitted_uses',
        'attribution',
        'evidence_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'permitted_uses' => 'array',
        ];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isCurrentlyValid(): bool
    {
        return $this->status === 'active'
            && ($this->valid_from === null || $this->valid_from->lessThanOrEqualTo(now()))
            && ($this->valid_until === null || $this->valid_until->greaterThanOrEqualTo(now()));
    }
}
