<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory, HasMedia, HasSeoMeta;

    protected $fillable = [
        'course_section_id','title','slug','type','summary','content',
        'duration_seconds','is_free','status','published_at','sort_order'
    ];

    protected $casts = ['is_free'=>'boolean','published_at'=>'datetime'];

    public function section(): BelongsTo { return $this->belongsTo(CourseSection::class, 'course_section_id'); }
    public function progress(): HasMany { return $this->hasMany(LessonProgress::class); }
}
