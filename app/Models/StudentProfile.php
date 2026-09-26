<?php
namespace App\Models;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class StudentProfile extends Model {
 use HasFactory, HasMedia;
 protected $fillable=['user_id','student_number','birth_date','grade','grade_id','school_name','bio','national_id_lookup','national_id_encrypted','registration_source','registered_by','onboarded_at','status'];
 protected $hidden=['national_id_encrypted'];
 protected $casts=['birth_date'=>'date','onboarded_at'=>'datetime'];
 public function user(): BelongsTo{return $this->belongsTo(User::class);}
 public function gradeRelation(): BelongsTo{return $this->belongsTo(AcademicGrade::class,'grade_id');}
 public function registeredBy(): BelongsTo{return $this->belongsTo(User::class,'registered_by');}
 public function parents(): BelongsToMany{return $this->belongsToMany(User::class,'parent_student','student_id','parent_id')->withPivot('relation')->withTimestamps();}
}