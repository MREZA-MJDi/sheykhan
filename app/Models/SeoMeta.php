<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory;
    protected $table='seo_metas';
    protected $fillable=['seoable_type','seoable_id','title','description','keywords','canonical_url','robots','og_title','og_description','og_image_url','schema_json'];
    protected $casts=['schema_json'=>'array'];
    public function seoable(): MorphTo { return $this->morphTo(); }
}
