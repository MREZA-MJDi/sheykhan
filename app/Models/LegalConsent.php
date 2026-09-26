<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LegalConsent extends Model {
 use HasFactory;
 protected $fillable=['user_id','document_id','order_id','subject_type','subject_id','document_version','consent_type','content_hash','ip_address','user_agent','accepted_at'];
 protected $casts=['accepted_at'=>'datetime'];
 public function user(): BelongsTo{return $this->belongsTo(User::class);}
 public function document(): BelongsTo{return $this->belongsTo(LegalDocument::class,'document_id');}
 public function order(): BelongsTo{return $this->belongsTo(Order::class);}
}