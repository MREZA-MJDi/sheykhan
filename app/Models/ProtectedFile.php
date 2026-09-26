<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProtectedFile extends Model {
 use HasFactory;
 protected $fillable=['product_file_id','entitlement_id','source_checksum','generated_path','watermark_text','watermark_type','generated_at','expires_at','status','download_count'];
 protected $casts=['generated_at'=>'datetime','expires_at'=>'datetime','download_count'=>'integer'];
 public function productFile(): BelongsTo{return $this->belongsTo(ProductFile::class);}
 public function entitlement(): BelongsTo{return $this->belongsTo(ProductEntitlement::class);}
}