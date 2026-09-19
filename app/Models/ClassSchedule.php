<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_id','weekday','start_time','end_time','room','meeting_url'];

    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
}
