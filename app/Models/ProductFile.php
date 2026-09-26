<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductFile extends Model {
 use HasFactory;
 protected $fillable=['product_id','media_id','version','is_primary','is_preview','requires_watermark'];
 protected $casts=['is_primary'=>'boolean','is_preview'=>'boolean','requires_watermark'=>'boolean'];
 public function product(): BelongsTo{return $this->belongsTo(Product::class);}
 public function media(): BelongsTo{return $this->belongsTo(Media::class);}
 public function downloads(): HasMany{return $this->hasMany(ProductDownload::class);}
 public function protectedFiles(): HasMany{return $this->hasMany(ProtectedFile::class);}
}