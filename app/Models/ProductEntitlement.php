<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductEntitlement extends Model {
 use HasFactory;
 protected $fillable=['user_id','product_id','order_item_id','status','starts_at','expires_at','granted_at'];
 protected $casts=['starts_at'=>'datetime','expires_at'=>'datetime','granted_at'=>'datetime'];
 public function user(): BelongsTo{return $this->belongsTo(User::class);}
 public function product(): BelongsTo{return $this->belongsTo(Product::class);}
 public function orderItem(): BelongsTo{return $this->belongsTo(OrderItem::class);}
 public function downloads(): HasMany{return $this->hasMany(ProductDownload::class,'entitlement_id');}
 public function protectedFiles(): HasMany{return $this->hasMany(ProtectedFile::class,'entitlement_id');}
}