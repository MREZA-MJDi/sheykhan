<?php
namespace App\Models;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LearningResource extends Model {
 use HasFactory, HasMedia;
 protected $fillable=['academy_id','course_id','classroom_id','lesson_id','media_id','uploaded_by','title','description','resource_type','visibility','release_at','downloadable','status','sort_order'];
 protected $casts=['release_at'=>'datetime','downloadable'=>'boolean'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function course(): BelongsTo{return $this->belongsTo(Course::class);}
 public function classroom(): BelongsTo{return $this->belongsTo(Classroom::class);}
 public function lesson(): BelongsTo{return $this->belongsTo(Lesson::class);}
 public function file(): BelongsTo{return $this->belongsTo(Media::class,'media_id');}
 public function uploader(): BelongsTo{return $this->belongsTo(User::class,'uploaded_by');}
}