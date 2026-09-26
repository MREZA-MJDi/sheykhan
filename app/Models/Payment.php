<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
 use HasFactory;
 protected $fillable=['order_id','gateway','authority','transaction_id','tracking_code','amount','currency','status','gateway_response','paid_at'];
 protected $casts=['amount'=>'integer','gateway_response'=>'array','paid_at'=>'datetime'];
 public function order(): BelongsTo{return $this->belongsTo(Order::class);}
}