<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Achievement extends Model {
 use HasFactory;
 protected $fillable=['academy_id','student_id','grade_id','academic_year_id','display_name','achievement_type','school_name','title','description','media_id','status','is_featured','published_at','created_by'];
 protected $casts=['is_featured'=>'boolean','published_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function student(): BelongsTo{return $this->belongsTo(User::class,'student_id');}
 public function grade(): BelongsTo{return $this->belongsTo(AcademicGrade::class);}
 public function academicYear(): BelongsTo{return $this->belongsTo(AcademicYear::class);}
 public function media(): BelongsTo{return $this->belongsTo(Media::class,'media_id');}
 public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
}