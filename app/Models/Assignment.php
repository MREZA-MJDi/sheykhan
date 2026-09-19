<?php
namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory, HasMedia;
    protected $fillable=['course_id','classroom_id','teacher_id','title','instructions','due_at','max_score','status'];
    protected $casts=['due_at'=>'datetime','max_score'=>'decimal:2'];
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
}
