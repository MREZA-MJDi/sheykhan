<?php
namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPost extends Model
{
    use HasFactory, HasMedia, HasSeoMeta;
    protected $fillable=['category_id','author_id','title','slug','excerpt','content','status','published_at'];
    protected $casts=['published_at'=>'datetime'];
    public function category(): BelongsTo { return $this->belongsTo(BlogCategory::class,'category_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }
    public function tags(): BelongsToMany { return $this->belongsToMany(BlogTag::class,'blog_post_tag')->withTimestamps(); }
}
