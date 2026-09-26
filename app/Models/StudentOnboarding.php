<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StudentOnboarding extends Model {
 use HasFactory;
 protected $fillable=['academy_id','admin_id','student_id','entered_name','national_id_lookup','requested_grade_id','school_name','mobile','source','status','notes','verified_at','activated_at'];
 protected $casts=['verified_at'=>'datetime','activated_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function admin(): BelongsTo{return $this->belongsTo(User::class,'admin_id');}
 public function student(): BelongsTo{return $this->belongsTo(User::class,'student_id');}
 public function requestedGrade(): BelongsTo{return $this->belongsTo(AcademicGrade::class,'requested_grade_id');}
}