<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CourseEnrollment extends Model {
 use HasFactory;
 protected $fillable=['course_id','student_id','classroom_id','academic_year_id','registered_by','registration_source','status','paid_amount','started_at','completed_at'];
 protected $casts=['paid_amount'=>'decimal:2','started_at'=>'datetime','completed_at'=>'datetime'];
 public function course(): BelongsTo{return $this->belongsTo(Course::class);}
 public function student(): BelongsTo{return $this->belongsTo(User::class,'student_id');}
 public function classroom(): BelongsTo{return $this->belongsTo(Classroom::class);}
 public function academicYear(): BelongsTo{return $this->belongsTo(AcademicYear::class);}
 public function registeredBy(): BelongsTo{return $this->belongsTo(User::class,'registered_by');}
}