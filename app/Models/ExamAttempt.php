<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory;
    protected $fillable=['exam_id','student_id','attempt_number','started_at','submitted_at','score','status'];
    protected $casts=['started_at'=>'datetime','submitted_at'=>'datetime','score'=>'decimal:2'];
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function answers(): HasMany { return $this->hasMany(ExamAnswer::class); }
}
