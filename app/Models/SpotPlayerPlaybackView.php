<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SpotPlayerPlaybackView extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'spot_player_license_id',
        'event_type',
        'ip_address',
        'user_agent',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return ['viewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(SpotPlayerLicense::class, 'spot_player_license_id');
    }
}
