<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'academy_id', 'user_id', 'display_name', 'role', 'content_text', 'status',
        'is_featured', 'sort_order', 'published_at', 'publication_consent_at',
        'publication_consent_method', 'publication_consent_reference',
        'publication_consent_for_minor', 'publication_consent_recorded_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'publication_consent_at' => 'datetime',
        'publication_consent_for_minor' => 'boolean',
    ];

    public function academy(): BelongsTo { return $this->belongsTo(Academy::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
