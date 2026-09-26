<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class AuditLog extends Model {
 use HasFactory;
 protected $fillable=['actor_id','action','subject_type','subject_id','old_values','new_values','ip_address','user_agent'];
 protected $casts=['old_values'=>'array','new_values'=>'array'];
 public function actor(): BelongsTo{return $this->belongsTo(User::class,'actor_id');}
 public function subject(): MorphTo{return $this->morphTo();}
}