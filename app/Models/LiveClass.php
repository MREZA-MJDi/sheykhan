<?php
namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClass extends Model
{
    use HasFactory, HasMedia;
    protected $fillable=['course_id','classroom_id','teacher_id','recording_media_id','title','description','provider','meeting_url','scheduled_at','duration_minutes','status'];
    protected $casts=['scheduled_at'=>'datetime'];
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class,'teacher_id'); }
    public function recording(): BelongsTo { return $this->belongsTo(Media::class,'recording_media_id'); }
}
