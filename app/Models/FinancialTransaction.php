<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'academy_id','enrollment_id','user_id','recorded_by','type','status',
        'amount','currency','reference','idempotency_key','description','metadata','occurred_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function academy(): BelongsTo { return $this->belongsTo(Academy::class); }
    public function enrollment(): BelongsTo { return $this->belongsTo(CourseEnrollment::class, 'enrollment_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}