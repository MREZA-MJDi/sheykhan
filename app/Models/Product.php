<?php
namespace App\Models;
use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Product extends Model {
 use HasFactory, HasMedia;
 protected $fillable=['academy_id','category_id','created_by','title','slug','subtitle','description','product_type','delivery_type','price','sale_price','currency','status','is_featured','featured_from','featured_until','published_at'];
 protected $casts=['price'=>'integer','sale_price'=>'integer','is_featured'=>'boolean','featured_from'=>'datetime','featured_until'=>'datetime','published_at'=>'datetime'];
 public function academy(): BelongsTo{return $this->belongsTo(Academy::class);}
 public function category(): BelongsTo{return $this->belongsTo(ProductCategory::class);}
 public function creator(): BelongsTo{return $this->belongsTo(User::class,'created_by');}
 public function files(): HasMany{return $this->hasMany(ProductFile::class);}
 public function orderItems(): HasMany{return $this->hasMany(OrderItem::class);}
 public function entitlements(): HasMany{return $this->hasMany(ProductEntitlement::class);}
}