<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword', 'keyword_key', 'cluster', 'intent', 'target_url',
        'priority', 'status', 'search_volume', 'volume_source',
        'target_position', 'last_observed_position', 'last_observed_at',
        'last_observed_source', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'search_volume' => 'integer',
            'target_position' => 'integer',
            'last_observed_position' => 'integer',
            'last_observed_at' => 'date',
        ];
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(SeoKeywordRanking::class)->orderByDesc('observed_on')->orderByDesc('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term): void {
            $like = '%' . addcslashes($term, '%_\\\\') . '%';
            $inner->where('keyword', 'like', $like)
                ->orWhere('cluster', 'like', $like)
                ->orWhere('target_url', 'like', $like)
                ->orWhere('notes', 'like', $like);
        });
    }
}
