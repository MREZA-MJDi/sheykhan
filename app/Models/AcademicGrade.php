<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademicGrade extends Model {
 use HasFactory;
 protected $fillable=['code','title','sort_order','is_active'];
 protected $casts=['is_active'=>'boolean'];
 public function students(): HasMany{return $this->hasMany(StudentProfile::class,'grade_id');}
 public function classrooms(): HasMany{return $this->hasMany(Classroom::class,'grade_id');}
 public function courses(): BelongsToMany{return $this->belongsToMany(Course::class,'course_grade','grade_id','course_id')->withTimestamps();}
 public function achievements(): HasMany{return $this->hasMany(Achievement::class,'grade_id');}
}