<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HeroChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'prompt',
        'sequence',
        'explanation',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sequence' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(HeroChallengeOption::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
