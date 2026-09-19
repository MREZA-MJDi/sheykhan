<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentProfile extends Model
{
    use HasFactory, HasMedia;

    protected $fillable = ['user_id','student_number','birth_date','grade','school_name','bio'];

    protected $casts = ['birth_date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('relation')->withTimestamps();
    }
}
