<?php
namespace App\Models;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Testimonial extends Model {
 use HasFactory, HasMedia;
 protected $fillable=['academy_id','user_id','display_name','role','content_text','status','is_featured','sort_order','published_at'];
 protected $casts=['is_featured'=>'boolean','published_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function user(): BelongsTo{return $this->belongsTo(User::class);}
}