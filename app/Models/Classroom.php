<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Classroom extends Model {
 use HasFactory;
 protected $fillable=['academy_id','course_id','grade_id','academic_year_id','title','code','description','capacity','status','starts_at','ends_at'];
 protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function course(): BelongsTo{return $this->belongsTo(Course::class);}
 public function grade(): BelongsTo{return $this->belongsTo(AcademicGrade::class,'grade_id');}
 public function academicYear(): BelongsTo{return $this->belongsTo(AcademicYear::class);}
 public function teachers(): BelongsToMany{return $this->belongsToMany(User::class,'classroom_teacher','classroom_id','teacher_id')->withTimestamps();}
 public function students(): BelongsToMany{return $this->belongsToMany(User::class,'classroom_student','classroom_id','student_id')->withPivot(['status','enrolled_at','completed_at'])->withTimestamps();}
 public function schedules(): HasMany{return $this->hasMany(ClassSchedule::class);}
 public function attendance(): HasMany{return $this->hasMany(Attendance::class);}
 public function assignments(): HasMany{return $this->hasMany(Assignment::class);}
 public function exams(): HasMany{return $this->hasMany(Exam::class);}
 public function liveClasses(): HasMany{return $this->hasMany(LiveClass::class);}
 public function enrollments(): HasMany{return $this->hasMany(CourseEnrollment::class);}
}