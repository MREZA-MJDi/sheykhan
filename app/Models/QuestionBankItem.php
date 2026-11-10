<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankItem extends Model
{
    protected $fillable = [
        'academy_id', 'created_by', 'type', 'question', 'options',
        'correct_answers', 'expected_answer', 'default_score', 'topic',
        'difficulty', 'status',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'default_score' => 'decimal:2',
    ];

    public function academy(): BelongsTo
    {
        return $this->belongsTo(Academy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
