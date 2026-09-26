<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademyContentCategory extends Model {
 use HasFactory;
 protected $fillable=['academy_id','title','slug','sort_order','is_active'];
 protected $casts=['is_active'=>'boolean'];
 public function academy(): \Illuminate\Database\Eloquent\Relations\BelongsTo{return $this->belongsTo(Academy::class);}
 public function contents(): HasMany{return $this->hasMany(AcademyContent::class,'category_id');}
}