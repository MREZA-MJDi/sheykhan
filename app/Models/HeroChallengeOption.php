<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroChallengeOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_challenge_id',
        'label',
        'content',
        'is_correct',
        'sort_order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(HeroChallenge::class, 'hero_challenge_id');
    }
}
