<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoKeywordRanking extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'seo_keyword_id', 'observed_on', 'position', 'search_volume',
        'impressions', 'clicks', 'ctr', 'source', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'observed_on' => 'date',
            'position' => 'integer',
            'search_volume' => 'integer',
            'impressions' => 'integer',
            'clicks' => 'integer',
            'ctr' => 'decimal:4',
        ];
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SeoKeyword::class, 'seo_keyword_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
