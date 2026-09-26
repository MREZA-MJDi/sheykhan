<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class OrderItem extends Model {
 use HasFactory;
 protected $fillable=['order_id','product_id','beneficiary_id','product_title_snapshot','unit_price','quantity','total_price'];
 protected $casts=['unit_price'=>'integer','quantity'=>'integer','total_price'=>'integer'];
 public function order(): BelongsTo{return $this->belongsTo(Order::class);}
 public function product(): BelongsTo{return $this->belongsTo(Product::class);}
 public function beneficiary(): BelongsTo{return $this->belongsTo(User::class,'beneficiary_id');}
 public function entitlement(): HasOne{return $this->hasOne(ProductEntitlement::class);}
}