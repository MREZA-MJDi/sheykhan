<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LegalDocument extends Model {
 use HasFactory;
 protected $fillable=['code','title','version','content','content_hash','document_type','required_for_purchase','is_active','published_at'];
 protected $casts=['required_for_purchase'=>'boolean','is_active'=>'boolean','published_at'=>'datetime'];
 public function consents(): HasMany{return $this->hasMany(LegalConsent::class,'document_id');}
}