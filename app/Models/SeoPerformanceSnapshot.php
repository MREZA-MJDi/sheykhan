<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class SeoPerformanceSnapshot extends Model
{
    protected $fillable = [
        'date',
        'dimension',
        'page_hash',
        'page_url',
        'query_hash',
        'query',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clicks' => 'integer',
            'impressions' => 'integer',
            'ctr' => 'decimal:10',
            'position' => 'decimal:6',
            'synced_at' => 'datetime',
        ];
    }
}
