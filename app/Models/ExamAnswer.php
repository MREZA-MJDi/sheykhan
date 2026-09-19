<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory;
    protected $fillable=['exam_attempt_id','question_id','answer','is_correct','score'];
    protected $casts=['is_correct'=>'boolean','score'=>'decimal:2'];
    public function attempt(): BelongsTo { return $this->belongsTo(ExamAttempt::class,'exam_attempt_id'); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}
