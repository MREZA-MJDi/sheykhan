<?php
namespace App\Models;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AcademyContent extends Model {
 use HasFactory, HasMedia;
 protected $fillable=['academy_id','category_id','type','title','slug','excerpt','body','video_duration_seconds','status','is_featured','sort_order','published_at','created_by'];
 protected $casts=['is_featured'=>'boolean','published_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function category(): BelongsTo{return $this->belongsTo(AcademyContentCategory::class,'category_id');}
 public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
}