<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Order extends Model {
 use HasFactory;
 protected $fillable=['order_number','buyer_id','status','currency','subtotal','discount','total','billing_name','billing_mobile','legal_consent_completed','paid_at'];
 protected $casts=['subtotal'=>'integer','discount'=>'integer','total'=>'integer','legal_consent_completed'=>'boolean','paid_at'=>'datetime'];
 public function buyer(): BelongsTo{return $this->belongsTo(User::class,'buyer_id');}
 public function items(): HasMany{return $this->hasMany(OrderItem::class);}
 public function payments(): HasMany{return $this->hasMany(Payment::class);}
 public function consents(): HasMany{return $this->hasMany(LegalConsent::class);}
}