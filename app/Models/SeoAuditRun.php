<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SeoAuditRun extends Model
{
    protected $fillable = [
        'started_by',
        'status',
        'active_key',
        'target_url',
        'pages_found',
        'pages_scanned',
        'issues_count',
        'critical_count',
        'warning_count',
        'info_count',
        'duration_ms',
        'summary',
        'results',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'results' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['queued', 'running'], true);
    }
}