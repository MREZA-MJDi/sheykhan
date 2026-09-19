<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    use HasFactory;

    protected $table='lesson_progress';
    protected $fillable=['lesson_id','user_id','progress_percent','seconds_watched','completed_at','last_watched_at'];
    protected $casts=['progress_percent'=>'decimal:2','completed_at'=>'datetime','last_watched_at'=>'datetime'];

    public function lesson(): BelongsTo { return $this->belongsTo(Lesson::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
