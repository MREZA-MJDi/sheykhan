<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TeacherDirectMessage extends Model
{
    protected $fillable = [
        'academy_id', 'teacher_id', 'sender_id', 'sender_type', 'recipient_type', 'recipient_id', 'student_id',
        'course_id', 'classroom_id', 'category', 'subject', 'body', 'response',
        'responded_by', 'responded_at', 'status',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function academy(): BelongsTo { return $this->belongsTo(Academy::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function recipient(): BelongsTo { return $this->belongsTo(User::class, 'recipient_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function responder(): BelongsTo { return $this->belongsTo(User::class, 'responded_by'); }
}
