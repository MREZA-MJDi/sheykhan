<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory, HasMedia, HasSeoMeta;

    protected $fillable = [
        'academy_id','created_by','title','slug','short_description','description',
        'level','status','access_type','price','duration_minutes','published_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function academy(): BelongsTo { return $this->belongsTo(Academy::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'course_teacher','course_id','teacher_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function enrollments(): HasMany { return $this->hasMany(CourseEnrollment::class); }
    public function sections(): HasMany { return $this->hasMany(CourseSection::class)->orderBy('sort_order'); }
    public function classrooms(): HasMany { return $this->hasMany(Classroom::class); }
    public function assignments(): HasMany { return $this->hasMany(Assignment::class); }
    public function exams(): HasMany { return $this->hasMany(Exam::class); }
    public function liveClasses(): HasMany { return $this->hasMany(LiveClass::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at?->isPast();
    }

    public function isFree(): bool
    {
        return $this->access_type === 'free';
    }

    public function requiresPayment(): bool
    {
        return $this->access_type === 'paid';
    }
}
