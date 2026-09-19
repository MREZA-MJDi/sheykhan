<?php
namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory, HasMedia;
    protected $fillable=['assignment_id','student_id','content','submitted_at','score','feedback','graded_at','graded_by'];
    protected $casts=['submitted_at'=>'datetime','score'=>'decimal:2','graded_at'=>'datetime'];
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function grader(): BelongsTo { return $this->belongsTo(User::class,'graded_by'); }
}
