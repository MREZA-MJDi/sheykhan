<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;
    protected $fillable=['course_id','classroom_id','teacher_id','title','description','duration_minutes','starts_at','ends_at','attempts_allowed','status'];
    protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function questions(): HasMany { return $this->hasMany(Question::class); }
    public function attempts(): HasMany { return $this->hasMany(ExamAttempt::class); }
}
