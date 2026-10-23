<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AcademyMessage extends Model
{
    protected $fillable = [
        'academy_id', 'sender_id', 'sender_role', 'audience', 'recipient_id',
        'student_id', 'classroom_id', 'course_id', 'category', 'subject', 'body', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function academy(): BelongsTo { return $this->belongsTo(Academy::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
    public function recipient(): BelongsTo { return $this->belongsTo(User::class, 'recipient_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
