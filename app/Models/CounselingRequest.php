<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CounselingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'academy_id',
        'parent_id',
        'student_id',
        'subject',
        'details',
        'preferred_at',
        'scheduled_at',
        'proposed_at',
        'proposed_by',
        'proposal_notes',
        'status',
        'response_notes',
        'handled_by',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'proposed_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }
}
