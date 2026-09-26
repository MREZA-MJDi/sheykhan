<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductDownload extends Model {
 public $timestamps=false;
 use HasFactory;
 protected $fillable=['entitlement_id','product_file_id','ip_address','user_agent','downloaded_at'];
 protected $casts=['downloaded_at'=>'datetime'];
 public function entitlement(): BelongsTo{return $this->belongsTo(ProductEntitlement::class,'entitlement_id');}
 public function productFile(): BelongsTo{return $this->belongsTo(ProductFile::class);}
}