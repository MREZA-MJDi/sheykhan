<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = [
        'exam_id',
        'type',
        'question',
        'options',
        'correct_answer',
        'score',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'score' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
