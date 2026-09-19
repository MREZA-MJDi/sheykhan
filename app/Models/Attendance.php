<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable=['classroom_id','student_id','marked_by','attendance_date','status','note'];
    protected $casts=['attendance_date'=>'date'];
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function student(): BelongsTo { return $this->belongsTo(User::class,'student_id'); }
    public function marker(): BelongsTo { return $this->belongsTo(User::class,'marked_by'); }
}
