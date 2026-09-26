<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademicYear extends Model {
 use HasFactory;
 protected $fillable=['title','start_date','end_date','is_current'];
 protected $casts=['start_date'=>'date','end_date'=>'date','is_current'=>'boolean'];
 public function classrooms(): HasMany { return $this->hasMany(Classroom::class); }
 public function enrollments(): HasMany { return $this->hasMany(CourseEnrollment::class); }
}